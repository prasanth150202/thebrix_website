// Lightweight, dependency-free retrieval over the knowledge/*.md files.
//
// Each markdown file is split into sections on "## " headings (the "# " title
// is kept as context for every section in that file). At request time the
// question is scored against every section by keyword overlap and only the
// top matches are returned — the full knowledge base is never sent in one
// shot. Sections are parsed once per cold start and cached in module scope.

const fs = require('fs');
const path = require('path');

const KNOWLEDGE_DIR = path.join(__dirname, '..', 'knowledge');

const STOPWORDS = new Set([
  'the', 'a', 'an', 'and', 'or', 'but', 'is', 'are', 'was', 'were', 'be', 'been',
  'to', 'of', 'in', 'on', 'for', 'with', 'as', 'at', 'by', 'it', 'this', 'that',
  'my', 'your', 'i', 'you', 'we', 'do', 'does', 'did', 'can', 'how', 'what',
  'where', 'when', 'why', 'which', 'who', 'will', 'would', 'should', 'could',
  'have', 'has', 'had', 'not', 'no', 'yes', 'if', 'so', 'than', 'then', 'from',
  'about', 'into', 'up', 'out', 'get', 'me', 'am', 'its', 'their', 'there',
  'today', 'now', 'here', 'please', 'just', 'want', 'like', 'need', 'also',
  'any', 'some', 'all', 'one', 'two', 'more', 'much', 'many', 'other',
]);

const tokenize = text =>
  (text.toLowerCase().match(/[a-z0-9]+/g) || [])
    .filter(w => w.length > 2 && !STOPWORDS.has(w));

let sectionsCache = null;

/**
 * Parse every knowledge/*.md file into {file, title, heading, text, tokens} sections.
 * Cached at module scope so warm serverless invocations don't re-read disk.
 */
function loadSections() {
  if (sectionsCache) return sectionsCache;

  const sections = [];
  let files = [];
  try {
    files = fs.readdirSync(KNOWLEDGE_DIR).filter(f => f.endsWith('.md'));
  } catch (err) {
    console.error('Could not read knowledge/ directory:', err.message);
    sectionsCache = [];
    return sectionsCache;
  }

  for (const file of files) {
    const raw = fs.readFileSync(path.join(KNOWLEDGE_DIR, file), 'utf8');
    const lines = raw.split('\n');

    let title = file.replace(/\.md$/, '');
    let heading = title;
    let buffer = [];

    const flush = () => {
      const text = buffer.join('\n').trim();
      if (text) {
        sections.push({
          file,
          title,
          heading,
          text,
          tokens: new Set(tokenize(`${heading} ${text}`)),
        });
      }
      buffer = [];
    };

    for (const line of lines) {
      const h1 = line.match(/^#\s+(.*)/);
      const h2 = line.match(/^##\s+(.*)/);
      if (h1) {
        flush();
        title = h1[1].trim();
        heading = title;
        continue;
      }
      if (h2) {
        flush();
        heading = h2[1].trim();
        continue;
      }
      buffer.push(line);
    }
    flush();
  }

  sectionsCache = sections;
  return sectionsCache;
}

/**
 * Score every section against the query by keyword overlap. Heading matches
 * count double since a heading hit is a strong topical signal.
 */
function scoreSections(query) {
  const sections = loadSections();
  const queryTokens = tokenize(query);
  if (queryTokens.length === 0) return sections.map(s => ({ section: s, score: 0 }));

  const headingTokenSets = sections.map(s => new Set(tokenize(s.heading)));

  return sections.map((section, i) => {
    let score = 0;
    for (const t of queryTokens) {
      if (headingTokenSets[i].has(t)) score += 2;
      else if (section.tokens.has(t)) score += 1;
    }
    return { section, score };
  });
}

/**
 * Return the top-K most relevant sections for a question. Falls back to a
 * small default set (overview + features) if nothing scores above zero, so
 * a legitimate but oddly-phrased BRIX question still gets grounded context.
 */
function retrieveRelevant(query, topK = 5) {
  const scored = scoreSections(query).sort((a, b) => b.score - a.score);
  const hits = scored.filter(s => s.score > 0).slice(0, topK);

  if (hits.length > 0) return hits.map(h => h.section);

  const sections = loadSections();
  return sections.filter(s => s.file === 'overview.md' || s.file === 'features.md');
}

/** Highest score across all sections — used by the scope guard. */
function bestScore(query) {
  const scored = scoreSections(query);
  return scored.reduce((max, s) => Math.max(max, s.score), 0);
}

function formatContext(sections) {
  return sections
    .map(s => `### ${s.title}${s.heading !== s.title ? ' — ' + s.heading : ''}\n${s.text}`)
    .join('\n\n---\n\n');
}

module.exports = { loadSections, retrieveRelevant, bestScore, formatContext, tokenize };
