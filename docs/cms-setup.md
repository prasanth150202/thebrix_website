# Brix CMS: deployment and day-to-day use

The site now runs on PHP with a MySQL database. Articles live in the
database as markdown and are rendered to match the existing design
automatically. Everything is managed at `thebrix.io/admin`.

---

## Part 1: first deployment

Do these in order. Steps 1 to 3 take about five minutes.

### 1. Create the database

In Hostinger hPanel go to **Databases → MySQL Databases** and create a
new database and user. Write down four things:

- database name (usually prefixed, e.g. `u123456789_brix`)
- database user
- database password
- host (almost always `localhost`)

### 2. Push the code

Deploy this branch the way you normally do. Nothing is live yet: the
site keeps working from the moment the files land, because `.htaccess`
maps every old URL onto the new PHP files.

### 3. Run the installer

Visit **`https://thebrix.io/admin/setup.php`** and fill in the four
database values plus the admin username and password you want.

It creates the five tables, creates your account, writes
`includes/config.php`, and then locks itself so it cannot run again.

> The installer is reachable without a login, which is unavoidable on a
> first run. It is safe because it cannot do anything without working
> database credentials, and it refuses to run at all once an admin
> account exists.

### 4. Import the existing articles

The installer links you to **`/admin/migrate.php`**. Run it once. It
imports the eight original articles from `content/migrated/`, all with
the author **Admin**, all keeping their existing URLs.

Re-running it is harmless: anything already imported is skipped, never
overwritten.

### 5. Check, then tidy up

Open these and confirm they look right:

- `thebrix.io/blog.html` and `thebrix.io/case-studies.html`
- any one article, e.g. `thebrix.io/blog-cart-upsell-examples.html`
- `thebrix.io/sitemap.xml` (should list all eight articles)
- `thebrix.io/contact.html`

Then **delete `admin/setup.php` from the server**. It is locked, but
there is no reason to leave an installer in a public folder.

---

## Part 2: writing posts

Sign in at `thebrix.io/admin`.

### How saving works

Nothing you type is public until you press **Publish**. It behaves the
way staging and committing does:

| Action | What happens |
| --- | --- |
| Typing | Autosaves to a draft every minute |
| **Preview** | Saves the draft first, then opens the real page |
| **Save as draft** | Saves and returns to the post list. Not public |
| **Publish** | Asks to confirm, then makes it live |

The important part: **editing a post that is already live does not
change the live page.** Those edits are held to one side and the public
keeps seeing the last published version until you press Publish. The
post list marks such posts *edits pending*, and you can throw the
pending edits away and go back to what is live.

A live post can be taken down again at any time with **Unpublish and
return it to draft** in the editor.

### Adding a post

1. **New post**
2. Choose **Blog post** or **Case study**. This decides the URL prefix
   (`blog-` or `case-study-`) and which listing page it appears on.
3. Paste your markdown into the body box, **or** click *Load .md file*
   and pick a file. Loading a file fills the fields in and leaves the
   text editable.
4. Set the **Author** to whatever name should appear on the article.
5. Click **Auto-fill fields** to fill any box you left empty: the title
   from the first `# heading`, the hero subtitle and card excerpt from
   the first paragraph, and the read time from the word count. It never
   overwrites something you typed.
6. **Preview** to see the real page.
7. **Publish** when you are happy.

The post now appears on its listing page, in the footer of every page,
and in `sitemap.xml`, with no further work.

### The web address

The box under the title holds the last part of the address. The
`/blog/` or `/case-study/` in front of it comes from the post's type,
so an address can never disagree with the list the post appears on.

On a new post it follows the title until you type your own, and is then
left exactly as you typed it. Emptying the box hands it back to the
title.

Changing the address of a post that is already live moves the page when
you publish. The address it had keeps working: it permanently redirects
to the new one, so inbound links and search results do not break, and
the editor lists every old address under the box. `sitemap.xml` is
generated from the database, so it picks the new address up on its own.

An address that another post uses, or that another post has moved away
from, is refused rather than quietly numbered.

### Search engine listing

Entirely optional. The meta title and description boxes only exist to
show Google *different* wording from the post's own title and excerpt.
Leave them empty and the post's own values are used. Either way the
page is published and listed in the sitemap; the preview panel always
shows exactly what will go out.

### Editing

Open any post from the list. You can:

- edit the markdown in place,
- click *Load .md file* to replace the body wholesale,
- click **download .md** to get the file, edit it offline, and load it
  back in. The downloaded file carries all its settings in a header
  block, which is read back when you upload it.

### Deleting

Deleting asks you to type the post title. It then moves to *Recently
deleted*, where it can be restored (as a draft). Only from there can it
be erased permanently.

### Images

Use **Insert image** in the editor. Files go to `assets/blog/` and the
markdown reference is inserted at your cursor. JPG, PNG, GIF, WebP and
SVG, up to 4 MB.

### Hero background image

Optional, per post, in the **Card & hero** panel. Without one the hero
keeps the plain gradient it has always had, which is still the default.

**Upload image** puts the file in `assets/blog/` like any other image and
shows it in the thumbnail. **Blur** runs from 0 to 24px and is only
available once an image is set; removing the image resets it to 0.

Two things worth knowing:

- The image sits under a light wash, and the title stays dark. That is
  what keeps the heading readable whatever you upload, so a photo will
  look softer on the page than it does on your desktop. Blur pushes it
  further back when the photo is busy enough to compete with the title.
- The thumbnail in the editor carries the same wash and the same blur as
  the real page, so you can judge it there without opening a preview.

Landscape images around 2000px wide are the right shape. The hero is
wide and short, so anything portrait gets cropped to its middle.

---

## Part 3: how markdown becomes a styled page

Write plain markdown. The renderer handles the styling:

| You write | You get |
| --- | --- |
| `## Heading` | Site-styled H2, with an anchor link |
| First paragraph | The larger intro paragraph |
| `**bold**`, lists, tables | Styled to match existing articles |
| `# Title` at the top | Removed, because the hero already shows it |
| A link to another site | Opens in a new tab automatically |

Two details worth knowing:

- **`## **Bold heading**` is normalised.** Some source files wrap
  headings in `**`. The extra bold is stripped so every heading looks
  the same.
- **Raw HTML passes through.** If a page needs something markdown
  cannot express, paste the HTML straight into the body. This is how
  you would restore a stats band on a case study (see below).

---

## Part 4: what changed about case studies

The four case studies kept their prose, headings, tables, publish
dates, categories and **their listing-page cards including the
headline figures** (`+26%`, `₹1,480 → ₹1,860`, and so on) — those are
plain text fields on the post, so they survived.

What the article pages lost, because markdown cannot express it:

- the animated counter band at the top (`2x`, `₹14L/mo`)
- the metric bar charts
- the app promo card with the star rating
- the hero metrics panel and the mid-article install prompt

This was the agreed trade for having every article edited the same way.

**Nothing is gone permanently.** The original HTML is kept in three
places: git history, `content/original-html/`, and the converted
markdown in `content/migrated/`. To bring a block back on one page,
copy its HTML out of `content/original-html/` and paste it into that
post's markdown body.

---

## Part 5: contact form and newsletter

Both write to the database and are read under **Submissions** in the
admin, with CSV export.

- **Contact** (`/contact.html`): name, email, store URL (optional),
  message. No email is sent, so check the panel. Protected by a
  honeypot field, a minimum submit time, and a limit of five messages
  per hour per IP.
- **Newsletter**: the footer form on every page. It previously only
  *looked* like it worked; it discarded every address. It now stores
  the address along with the page and the campaign that brought the
  visitor in.

---

## Part 6: known items

**The AI chat widget is still broken.** `js/main.js` posts to
`/api/chat`, which is a Node function that PHP hosting does not serve.
The widget silently falls back to one canned reply, so it looks like it
works. Porting `api/chat.js` to PHP is separate work; `.htaccess`
deliberately leaves `/api/` alone.

**Everything else has been tested against a real MySQL server.** The
install, the migration of all eight articles, every page, the admin
login and lockout, the full draft/publish/delete/restore/erase cycle,
both forms and the image uploader were all exercised end to end
against MariaDB 10.4 on a throwaway database, which was then dropped.

Two bugs were found and fixed during that run, both of which would
have reached production:

- **The FAQ sections were being dropped.** The converter had no rule
  for the `<details>` accordions at the bottom of each case study, so
  roughly 150 words of question-and-answer text per case study
  disappeared silently. They are now converted to headings and
  paragraphs. A word-level comparison of every article against its
  original now shows zero unexplained loss.
- **The contact form never worked.** Its CSRF token was the first
  thing to touch the session, and it runs inside the form markup,
  by which point the session cookie can no longer be sent. Every
  submission failed validation. The page now opens the session before
  any output, and `csrf_token()` writes a loud error to the log if it
  is ever called too late again.

---

## Reference

### Layout

```
admin/            the panel (setup, login, editor, submissions)
includes/         shared code; blocked from the web
  bootstrap.php     config, database, sessions
  markdown.php      markdown to styled article HTML
  header/footer.php shared page chrome
  article-view.php  the article template
  schema.php        table definitions
  config.php        credentials, written by setup, never committed
content/
  migrated/         markdown for the eight original articles
  original-html/    the pre-migration HTML, for reference
article.php       renders any post by slug
sitemap.php       generates sitemap.xml from the database
```

### Deploying a code update

Push the files. Nothing else.

When a release adds a database column, the panel adds it itself the
first time an admin page is opened after the deploy, so there is never
any SQL to run by hand. The check is listed in `includes/schema.php`
under `brix_added_columns()` and skips anything already present, so it
is safe on every deploy and on a fresh install alike.

### Restoring the old site

Everything replaced is in git:

```bash
git checkout HEAD~1 -- '*.html' sitemap.xml
```

Then remove `.htaccess`, and the static site is back exactly as it was.

### Rotating the admin password

There is no password-reset screen. To change it, run this once from a
PHP file on the server (then delete the file):

```php
<?php
require __DIR__ . '/includes/bootstrap.php';
db()->prepare('UPDATE admin_users SET password_hash = ? WHERE username = ?')
    ->execute([password_hash('your-new-password', PASSWORD_DEFAULT), 'your-username']);
echo 'done';
```
