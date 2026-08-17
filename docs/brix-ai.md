# Brix AI — the site support chat

Status: **built and working, switched off.** Flip `BRIX_CHAT_ENABLED` in
`includes/bootstrap.php` to bring it back. Read the throughput note at the
bottom first, because that is the only reason it is off.

This replaces the Node/Vercel package the chat was originally delivered as.
Nothing here runs on Node; the site is PHP on Apache and so is the chat.

## How a question is answered

`POST /api/chat` with `{"message": "...", "history": [...]}` returns
`{"reply": "..."}` as plain text. `.htaccess` routes the extensionless path
to `api/chat.php`. The widget lives in `js/main.js` (`brixChat()`), styled by
the `.bx-` rules in `css/styles.css`.

Four layers, cheapest first:

1. **Injection guard** — `api/guardrails.php`. Regex patterns, no API call.
   Returns a fixed refusal.
2. **Classifier** — the model is shown the 58 topics in `api/answers.php` and
   must reply with only an id, or `0`. On a hit the curated answer is
   returned **verbatim**: the model picks which answer, never its wording, so
   the common path cannot go off-message.
3. **Scope guard** — runs *after* classification, deliberately. It asks
   whether the knowledge base has anything to say about the question, and
   several curated topics cover things the knowledge base does not. Classify
   first, and the guard is left gating only the generative fallback.
   A message that tokenizes to nothing is all stopwords — small talk, not an
   off-topic question — so it is allowed through rather than refused.
4. **Retrieval fallback** — `api/retrieval.php` scores every `## ` section in
   `knowledge/*.md` by keyword overlap (headings count double) and the top 5
   become the model's only source of truth. Purely lexical: no embeddings, no
   vector store, no index to rebuild. Editing a file in `knowledge/` is the
   whole deployment step.

## Files

| Path | What it is |
| --- | --- |
| `api/chat.php` | The endpoint. Both model calls and the ordering above. |
| `api/answers.php` | 58 curated topics. Nowdoc strings so `$29` stays literal. |
| `api/retrieval.php` | Section parsing and keyword scoring over `knowledge/`. |
| `api/guardrails.php` | Injection patterns, refusal text, scope guard. |
| `api/.htaccess` | Denies `.js`/`.json`/`.md` here. Defence in depth. |
| `knowledge/*.md` | The corpus. 20 files, ~104 sections. |
| `js/main.js` | The widget, gated on `window.BRIX_CHAT`. |

## Writing knowledge files

Retrieval is lexical, so **a section is only findable by the words it
contains.** This is the failure mode to watch: `troubleshooting.md` was
originally written as specific symptoms ("Nothing appears on my storefront")
and contained none of the words a frustrated merchant actually types — "not
working", "bug", "issue", "problem", "fix" appeared zero times. Questions
about a broken app retrieved blog posts instead. The fix was a triage section
using the reader's vocabulary, not the author's.

There is no stemming, so `bug` and `bugged` are unrelated tokens. When a
topic matters, spell out the forms people use.

## Prompts

Both prompts live in `api/chat.php` and are tuned for a small free model, not
a frontier one. Two things were learned the hard way:

- The classifier must be told to **prefer `0` over a merely related topic**.
  Asked for the "best match" it always finds one, so every reward-shaped
  question came back with the reward setup steps and the fallback never ran.
- The system prompt for the fallback is deliberately short. The original
  enumerated everything the assistant must never disclose, down to "internal
  business logic", each with a fixed refusal string. A 26B model does not
  weigh a list like that, it pattern matches against it — asking why a
  progress bar works on shoppers tripped the disclosure rule and returned the
  refusal verbatim. Injection is already caught deterministically in layer 1.

## Configuration

`OPENROUTER_API_KEY` in `.env` at the project root, loaded by
`brix_load_env()`. `.htaccess` denies `.env` by name and `.gitignore` covers
it at any depth.

**Never put the key in a markdown file.** The original package shipped it in
plaintext in its README, which `.gitignore` did not cover, and that key should
be treated as compromised until rotated.

Models are `google/gemma-4-26b-a4b-it:free` (primary) and
`openai/gpt-oss-20b:free` (fallback), both $0 per token.

## Why it is switched off

Throughput, not correctness. OpenRouter's free tier allows **50 requests per
day per account** and **20 per minute**. A curated answer costs one request,
a fallback costs two, so the site gets 25–50 questions a day before every
visitor is told Brix AI is unavailable.

Buying **10 credits once** at `openrouter.ai/settings/credits` raises the
daily allowance to 1000. The credits are not consumed by these models, since
they bill at zero per token — the purchase is a threshold, not a balance to
spend. The 20/minute ceiling is unaffected and is the next limit to meet.

Failure is already handled: a 429 short-circuits instead of retrying the
second model (both draw on the same account bucket) and the reply says the
service is unavailable rather than blaming the question.

## Known loose end

The `troubleshooting.md` triage section was verified at the retrieval layer —
it ranks first — but the final answer wording was never confirmed end to end,
because the daily quota ran out mid-test. Re-run that one question first.
