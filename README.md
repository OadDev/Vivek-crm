# Vivek Jain CRM — Communication Management System

A Laravel + MySQL backend for the Communication Management System: Gmail-style
inbox, Contacts, WhatsApp Templates, and Product Master, with a first-run
Setup Wizard for database configuration.

## Requirements

- PHP 8.2+
- Composer
- MySQL 8.x (or MariaDB)

## Deployment

Pushes to `main` auto-deploy to Hostinger via GitHub Actions. See
[DEPLOYMENT.md](DEPLOYMENT.md) for the one-time setup (SSH keys, server
paths, GitHub secrets) and how the pipeline works.

## Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
```

Then open the app in your browser. Since no database is configured yet, you'll
land on **`/setup`** — the Installation Wizard:

1. **Step 1 — Database Credentials**: enter your MySQL host/port/database/
   username/password and click "Test Connection & Continue". This validates
   connectivity before touching anything.
2. **Step 2 — Create Admin Account**: create the first administrator login.
   Optionally check "Load sample demo data" to explore the app with realistic
   contacts, products, templates and email threads already in place.

Submitting writes your DB credentials into `.env`, runs migrations, seeds the
copper standards reference table (and demo data if selected), creates your
admin user, and logs you straight into the dashboard.

> `.env` ships with `SESSION_DRIVER=file`, `CACHE_STORE=file` and
> `QUEUE_CONNECTION=sync` on purpose — the app must be able to boot (and
> serve the Setup Wizard itself) before any database tables exist.

## Scheduled automation

Two artisan commands drive the background automation described in the spec:

- `contacts:sync` — pulls contacts from the configured data source (an
  uploaded Excel file or a public Google Sheet link, configurable from the
  Contacts page's "Auto-Sync Data Source" panel) on the interval you set
  there. Status is computed from the sheet's date column: Active if
  contacted within 7 days, Follow-up between 7–20 days, Inactive after 20+
  days. A "Sync Now" button triggers it immediately.
- `contacts:recalculate-statuses` — runs daily and re-applies the same
  7-day / 20-day rule to every contact based on `last_contacted_at`, so
  statuses keep advancing automatically even without a fresh import.

Both are registered in `routes/console.php` via the Laravel scheduler. In
production, point a single system cron entry at it:

```bash
* * * * * cd /path-to-app && php artisan schedule:run >> /dev/null 2>&1
```

## Key modules

- **Dashboard** — live stats, weekly volume chart, activity feed.
- **Gmail Inbox** — folders, threaded conversations, reply-in-thread, star/
  archive, and "Create Contact" from an unmatched sender.
- **Contacts** — per-field custom filters (including WhatsApp and email),
  date-range filter, sortable columns, star-to-pin-to-top, Active/Follow-up/
  Inactive status automation, Excel import/export, and the auto-sync data
  source described above.
- **WhatsApp Templates** — `{name}` `{company}` `{employee}` `{date}`
  placeholders, and a real `wa.me` click-to-chat deep link on send (no send
  preview panel, by design).
- **Product Master** — Excel import/export, and a "Standard Copper
  Conductor Reference" quick-lookup popup (editable) next to the product
  table.
- **Settings** — profile, password, theme, Gmail connection toggle (UI-only
  placeholder — full OAuth sync is a future integration), WhatsApp defaults,
  system preferences.
