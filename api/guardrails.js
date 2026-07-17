// Pre-model guardrails: catch obvious prompt-injection attempts and clearly
// off-topic questions before spending an API call on them. These are a
// second, deterministic layer — the system prompt in chat.js also instructs
// the model to refuse the same categories, in case a paraphrase slips past
// the regexes below.

const { bestScore } = require('./retrieval');

// Gap-based (".{0,N}") rather than rigid word-order patterns, so inserted
// pronouns/filler ("show ME your HIDDEN instructions") don't slip past a
// pattern written for the exact phrase in the spec ("show your instructions").
const INJECTION_PATTERNS = [
  /\bignore\b.{0,25}\binstructions?\b/i,
  /\bdisregard\b.{0,25}\b(instructions?|rules?)\b/i,
  /\bforget\b.{0,25}\binstructions?\b/i,
  /\breveal\b.{0,25}\b(prompt|instructions?|documentation|context)\b/i,
  /\bshow\b.{0,25}\b(prompt|instructions?|documentation)\b/i,
  /\bprint\b.{0,25}\b(knowledge\s*base|prompt|instructions?)\b/i,
  /\bdump\b.{0,25}\bcontext\b/i,
  /\boutput\b.{0,25}\b(instructions?|prompt)\b/i,
  /\bwhat('?s|\s+is)\s+your\s+system\s+prompt\b/i,
  /\brepeat\b.{0,15}\babove\b/i,
  /\bprint\b.{0,15}\babove\b/i,
  /\bbypass\b.{0,25}\b(rules|restrictions|guidelines|filters)\b/i,
  /\bjailbreak\b/i,
  /\bdeveloper\s+mode\b/i,
  /\byou\s+are\s+now\b/i,
  /\bact\s+as\b.{0,25}\b(unrestricted|jailbroken|uncensored|different\s+ai)\b/i,
  /\bDAN\b/,
  /\bpretend\b.{0,20}\b(you\s+are|to\s+be)\b/i,
];

const REFUSALS = {
  injection:
    "I'm unable to share internal instructions or documentation. I'm happy to explain BRIX features from a merchant's perspective.",
  outOfScope:
    "I'm here to help with BRIX and its features. I can't answer unrelated questions. Feel free to ask about BRIX setup, pricing, troubleshooting, or feature guidance.",
};

function isPromptInjection(message) {
  return INJECTION_PATTERNS.some(re => re.test(message));
}

// Small-talk that should reach the model even with zero keyword overlap
// against the knowledge base, so the assistant doesn't feel broken on a
// plain "hi" or "what can you do".
const GREETING_ALLOWLIST = /^(hi|hey|hello|yo|sup|thanks|thank you|ok|okay|help|what can you do|who are you|good (morning|afternoon|evening))\b/i;

function isOutOfScope(message) {
  const trimmed = message.trim();
  if (GREETING_ALLOWLIST.test(trimmed)) return false;
  return bestScore(trimmed) === 0;
}

module.exports = { isPromptInjection, isOutOfScope, REFUSALS };
