<?php
/**
 * Pre-model guardrails: catch obvious prompt-injection attempts and clearly
 * off-topic questions before spending an API call on them. These are a
 * second, deterministic layer; the system prompt in chat.php also instructs
 * the model to refuse the same categories, in case a paraphrase slips past
 * the regexes below.
 *
 * Ported from guardrails.js, which was byte-identical in both copies of the
 * Node backend.
 */

declare(strict_types=1);

require_once __DIR__ . '/retrieval.php';

/**
 * Gap-based (".{0,N}") rather than rigid word-order patterns, so inserted
 * pronouns/filler ("show ME your HIDDEN instructions") don't slip past a
 * pattern written for the exact phrase in the spec ("show your instructions").
 */
function brix_injection_patterns(): array
{
    return [
        '/\bignore\b.{0,25}\binstructions?\b/i',
        '/\bdisregard\b.{0,25}\b(instructions?|rules?)\b/i',
        '/\bforget\b.{0,25}\binstructions?\b/i',
        '/\breveal\b.{0,25}\b(prompt|instructions?|documentation|context)\b/i',
        '/\bshow\b.{0,25}\b(prompt|instructions?|documentation)\b/i',
        '/\bprint\b.{0,25}\b(knowledge\s*base|prompt|instructions?)\b/i',
        '/\bdump\b.{0,25}\bcontext\b/i',
        '/\boutput\b.{0,25}\b(instructions?|prompt)\b/i',
        '/\bwhat(\'?s|\s+is)\s+your\s+system\s+prompt\b/i',
        '/\brepeat\b.{0,15}\babove\b/i',
        '/\bprint\b.{0,15}\babove\b/i',
        '/\bbypass\b.{0,25}\b(rules|restrictions|guidelines|filters)\b/i',
        '/\bjailbreak\b/i',
        '/\bdeveloper\s+mode\b/i',
        '/\byou\s+are\s+now\b/i',
        '/\bact\s+as\b.{0,25}\b(unrestricted|jailbroken|uncensored|different\s+ai)\b/i',
        '/\bDAN\b/',
        '/\bpretend\b.{0,20}\b(you\s+are|to\s+be)\b/i',
    ];
}

function brix_refusals(): array
{
    return [
        'injection'  => "I'm unable to share internal instructions or documentation. I'm happy to explain BRIX features from a merchant's perspective.",
        'outOfScope' => "I'm here to help with BRIX and its features. I can't answer unrelated questions. Feel free to ask about BRIX setup, pricing, troubleshooting, or feature guidance.",
    ];
}

function brix_is_prompt_injection(string $message): bool
{
    foreach (brix_injection_patterns() as $pattern) {
        if (preg_match($pattern, $message) === 1) {
            return true;
        }
    }

    return false;
}

/**
 * Small-talk that should reach the model even with zero keyword overlap
 * against the knowledge base, so the assistant doesn't feel broken on a
 * plain "hi" or "what can you do".
 */
function brix_is_out_of_scope(string $message): bool
{
    $trimmed  = trim($message);
    $greeting = '/^(hi|hey|hello|yo|sup|thanks|thank you|cheers|ok|okay|help|what can you do|who are you'
        . '|how are you|how\'?re you|how r u|how(\'?s| is) it going|what\'?s up|nice to meet you'
        . '|good (morning|afternoon|evening|day))\b/i';

    if (preg_match($greeting, $trimmed) === 1) {
        return false;
    }

    // A message that tokenizes to nothing is made entirely of stopwords, so
    // there is no subject in it to be off-topic about — "how are you" scores
    // zero for the same reason a question about Shopify would, and used to be
    // told it was unrelated to BRIX. Small talk belongs to the model, which
    // answers it in a line and steers back; only a message that names
    // something the knowledge base has never heard of gets refused here.
    if (brix_tokenize($trimmed) === []) {
        return false;
    }

    return brix_best_score($trimmed) === 0;
}
