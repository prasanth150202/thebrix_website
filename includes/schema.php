<?php
/**
 * Database schema.
 *
 * Every statement is CREATE TABLE IF NOT EXISTS, so running this a
 * second time is harmless and it doubles as the upgrade path when a
 * new table is added later.
 */

declare(strict_types=1);

function brix_schema_statements(): array
{
    return [
        'admin_users' => "
            CREATE TABLE IF NOT EXISTS admin_users (
                id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
                username      VARCHAR(64)  NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                display_name  VARCHAR(120) NOT NULL DEFAULT 'Admin',
                last_login_at DATETIME     NULL,
                created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_username (username)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'login_attempts' => "
            CREATE TABLE IF NOT EXISTS login_attempts (
                id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                ip           VARCHAR(45)  NOT NULL,
                username     VARCHAR(100) NOT NULL DEFAULT '',
                attempted_at DATETIME     NOT NULL,
                PRIMARY KEY (id),
                KEY idx_ip_time (ip, attempted_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        // One row per article. `slug` holds the complete filename stem
        // ("blog-cart-upsell-examples") so a migrated post keeps the
        // exact URL it had as a static file.
        'posts' => "
            CREATE TABLE IF NOT EXISTS posts (
                id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
                type             ENUM('blog','case_study') NOT NULL DEFAULT 'blog',
                slug             VARCHAR(190) NOT NULL,
                title            VARCHAR(255) NOT NULL,
                author           VARCHAR(120) NOT NULL DEFAULT 'Admin',
                category         VARCHAR(80)  NOT NULL DEFAULT '',
                hero_subtitle    TEXT         NULL,
                /* Optional hero background. Empty means the plain
                   gradient hero, which is still the default. */
                hero_image       VARCHAR(255) NOT NULL DEFAULT '',
                hero_blur        TINYINT UNSIGNED NOT NULL DEFAULT 0,
                excerpt          TEXT         NULL,
                body_md          LONGTEXT     NOT NULL,
                read_minutes     SMALLINT UNSIGNED NOT NULL DEFAULT 5,
                date_published   DATE         NOT NULL,
                card_gradient    VARCHAR(20)  NOT NULL DEFAULT 'bshot-1',
                card_icon        VARCHAR(30)  NOT NULL DEFAULT 'cart',
                cta_heading      VARCHAR(180) NOT NULL DEFAULT '',
                cta_sub          VARCHAR(255) NOT NULL DEFAULT '',
                meta_title       VARCHAR(255) NOT NULL DEFAULT '',
                meta_description VARCHAR(320) NOT NULL DEFAULT '',
                status           ENUM('draft','published') NOT NULL DEFAULT 'draft',

                /* Edits to a post that is already live are staged here
                   rather than written straight to the columns above, so
                   the public page never changes until Publish is
                   pressed. JSON of the editable fields only. */
                draft_payload    LONGTEXT     NULL,
                draft_saved_at   DATETIME     NULL,

                deleted_at       DATETIME     NULL,
                created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
                                 ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_slug (slug),
                KEY idx_listing (type, status, deleted_at, date_published)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        /* Addresses a post has moved away from.

           Changing a live post's web address would otherwise break
           every link to the old one and throw away the ranking it has
           already earned. The old slug is kept here and permanently
           redirects to whatever the post's address is now. The row
           points at the post rather than at a replacement slug, so a
           post renamed twice still costs a visitor one redirect. */
        'post_redirects' => "
            CREATE TABLE IF NOT EXISTS post_redirects (
                id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
                old_slug   VARCHAR(190) NOT NULL,
                post_id    INT UNSIGNED NOT NULL,
                created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_old_slug (old_slug),
                KEY idx_post (post_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'newsletter_subscribers' => "
            CREATE TABLE IF NOT EXISTS newsletter_subscribers (
                id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
                email        VARCHAR(190) NOT NULL,
                source_page  VARCHAR(190) NOT NULL DEFAULT '',
                utm_source   VARCHAR(120) NOT NULL DEFAULT '',
                utm_medium   VARCHAR(120) NOT NULL DEFAULT '',
                utm_campaign VARCHAR(120) NOT NULL DEFAULT '',
                ip           VARCHAR(45)  NOT NULL DEFAULT '',
                created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_email (email)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        'contact_submissions' => "
            CREATE TABLE IF NOT EXISTS contact_submissions (
                id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
                name       VARCHAR(120) NOT NULL,
                email      VARCHAR(190) NOT NULL,
                store_url  VARCHAR(255) NOT NULL DEFAULT '',
                message    TEXT         NOT NULL,
                ip         VARCHAR(45)  NOT NULL DEFAULT '',
                user_agent VARCHAR(255) NOT NULL DEFAULT '',
                read_at    DATETIME     NULL,
                created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                /* When they last wrote in. created_at stays as the first
                   time, so the pair reads as known-since and last-heard */
                updated_at DATETIME     NULL,
                PRIMARY KEY (id),
                /* One row per person: writing in again updates the row
                   they already have rather than making a second one */
                UNIQUE KEY uniq_email (email),
                KEY idx_created (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    ];
}

/**
 * Columns added after a site was first installed.
 *
 * CREATE TABLE IF NOT EXISTS does nothing to a table that already
 * exists, so a column added to the definition above has to be listed
 * here as well or an established database never gets it.
 */
/**
 * Bump this whenever brix_added_columns() or brix_added_indexes() gains
 * an entry.
 *
 * The admin panel remembers that it has checked the schema so it does
 * not re-check on every page view, and it used to remember that in a
 * way a deploy could not clear: an admin whose session predated the
 * deploy skipped the upgrade entirely and then hit pages querying
 * columns the database did not have. The session now records which
 * version it checked, so a bump here invalidates it everywhere.
 */
define('BRIX_SCHEMA_VERSION', 4);

function brix_added_columns(): array
{
    return [
        ['posts', 'hero_image', "VARCHAR(255) NOT NULL DEFAULT '' AFTER hero_subtitle"],
        ['posts', 'hero_blur',  'TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER hero_image'],
        ['contact_submissions', 'updated_at', 'DATETIME NULL AFTER created_at'],
        /* Which form the row came from: '' for the contact page, or the
           slug of the landing page that captured it. Without it a
           campaign lead and a support enquiry look identical in the
           admin, and they need answering very differently. */
        ['contact_submissions', 'source', "VARCHAR(40) NOT NULL DEFAULT '' AFTER message"],
    ];
}

/**
 * Indexes added after a table first shipped.
 *
 * A unique index cannot go on a column that already holds duplicates,
 * so each entry carries the statement that collapses them first. The
 * contact table predates one-row-per-person, so any database that has
 * been taking enquiries already has repeats in it.
 */
function brix_added_indexes(): array
{
    return [
        [
            'contact_submissions',
            'uniq_email',
            'ALTER TABLE `contact_submissions` ADD UNIQUE KEY `uniq_email` (`email`)',
            // keep the most recent row for each address, drop the rest
            'DELETE older FROM `contact_submissions` older
             JOIN `contact_submissions` newer
               ON older.email = newer.email AND older.id < newer.id',
        ],
    ];
}

/**
 * Bring an existing database up to the definitions above.
 *
 * Returns the names of everything it added, so a caller can report
 * them. Each change is checked before it is made, which makes running
 * this on every admin session harmless. The table and column names
 * come from the lists above and never from a request, so interpolating
 * them into the ALTER is safe.
 */
function brix_upgrade_schema(PDO $pdo): array
{
    $added = [];

    // Every statement is CREATE TABLE IF NOT EXISTS, so this is a no-op
    // for the tables the database already has. It is also the only way
    // an established install picks up a table added later: the column
    // and index passes below cannot create one.
    foreach (brix_schema_statements() as $sql) {
        $pdo->exec($sql);
    }

    $exists = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t AND COLUMN_NAME = :c'
    );

    foreach (brix_added_columns() as [$table, $column, $definition]) {
        $exists->execute([':t' => $table, ':c' => $column]);

        if ((int) $exists->fetchColumn() > 0) {
            continue;
        }

        $pdo->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
        $added[] = $table . '.' . $column;
    }

    $hasIndex = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.STATISTICS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :t AND INDEX_NAME = :i'
    );

    foreach (brix_added_indexes() as [$table, $index, $create, $dedupe]) {
        $hasIndex->execute([':t' => $table, ':i' => $index]);

        if ((int) $hasIndex->fetchColumn() > 0) {
            continue;
        }

        if ($dedupe !== '') {
            $pdo->exec($dedupe);
        }

        $pdo->exec($create);
        $added[] = $table . '.' . $index;
    }

    return $added;
}

/** Create anything that is missing. Safe to call repeatedly. */
function brix_install_schema(PDO $pdo): void
{
    // Creates the tables and then adds anything a database made by an
    // earlier version of this file is missing.
    brix_upgrade_schema($pdo);
}
