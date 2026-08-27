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

## Running from two machines against one shared database

This app supports running from both a live Hostinger install and a local
XAMPP install at the same time, both reading/writing the **same** Hostinger
MySQL database — there is no separate local database.

1. **Enable Remote MySQL on Hostinger** (hPanel → Databases → Remote MySQL)
   and allow-list the IP address(es) the local machine connects from. This is
   the one step that can't be done from code — without it, Hostinger's MySQL
   simply refuses connections from outside its own server.
2. **Use identical `DB_HOST`/`DB_PORT`/`DB_DATABASE`/`DB_USERNAME`/
   `DB_PASSWORD` in both machines' `.env` files** — the Hostinger MySQL
   host, not XAMPP's local `127.0.0.1` one. See the comments above the
   `DB_*` block in `.env.example`.
3. **Only run the Setup Wizard once**, on whichever machine sets the database
   up first. On the second machine, since it's pointed at a database that
   already has users in it, the app detects that and skips straight to the
   login page — it will **not** show the wizard again or offer to load demo
   data a second time.
4. `CACHE_STORE=database` (the default in `.env.example`) is required for
   both installs to see each other's cache invalidations immediately — a
   file-based cache is per-machine and wouldn't pick up changes made on the
   other install.
5. If the local machine's internet connection drops, it can't reach the
   Hostinger database — the app shows a plain "Unable to connect to the
   Internet" page instead of a raw database error.

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

> `.env` ships with `SESSION_DRIVER=file` and `QUEUE_CONNECTION=sync` on
> purpose — the app must be able to boot (and serve the Setup Wizard itself)
> before any database tables exist. `CACHE_STORE=database` is safe alongside
> that since nothing is cached until after the first migration runs — see
> "Running from two machines against one shared database" below for why it's
> `database` and not `file`.

## Gmail Integration (real OAuth)

Settings → Gmail Integration connects a real Gmail account via Google OAuth
2.0 — not a placeholder. One-time setup, done by you (not in this repo or
chat):

1. In [Google Cloud Console](https://console.cloud.google.com/), create a
   project (or reuse one), enable the **Gmail API**, and configure the
   OAuth consent screen (Internal or External + your email as a test user
   is enough for personal use).
2. Create an **OAuth client ID** (Web application). Add an Authorized
   redirect URI matching exactly:
   `https://your-domain.example/settings/gmail/callback`
   (Settings → Gmail Integration → the ℹ️ instructions button shows this
   exact URL for your site, plus the localhost/XAMPP equivalent, with a
   copy button.)
3. Paste the resulting **Client ID** and **Client Secret** into the "Google
   OAuth Client" fields right there on the Settings page and click **Save
   Credentials** — no `.env` editing, SSH, or redeploy needed.
4. Click **Connect Gmail** — this redirects to Google's real consent
   screen. On approval, the account's inbox starts syncing (via the
   `gmail:sync` scheduled command, every 5 minutes, and immediately via
   **Sync Now**), and replies sent from the Gmail Inbox page go out through
   the real Gmail API, threaded onto the original conversation.

Client ID/Secret and the connected account's access/refresh tokens are all
stored encrypted in the `gmail_accounts` table — nothing Gmail-related lives
in `.env` unless you specifically prefer setting `GOOGLE_CLIENT_ID`/
`GOOGLE_CLIENT_SECRET`/`GOOGLE_REDIRECT_URI` that way instead (still
supported as a fallback when the Settings-page fields are empty).

## Scheduled automation

Three artisan commands drive the background automation described in the spec:

- `contacts:sync` — pulls contacts from the configured data source (an
  uploaded Excel file or a public Google Sheet link, configurable from the
  Contacts page's "Auto-Sync Data Source" panel) on the interval you set
  there. Status is computed from the sheet's date column: Active if
  contacted within 7 days, Follow-up between 7–20 days, Inactive after 20+
  days. A "Sync Now" button triggers it immediately.
- `contacts:recalculate-statuses` — runs daily and re-applies the same
  7-day / 20-day rule to every contact based on `last_contacted_at`, so
  statuses keep advancing automatically even without a fresh import.
- `gmail:sync` — pulls new inbox messages from the connected Gmail account
  every 5 minutes (no-op if nothing is connected).

**No server cron job is required.** Every page, once loaded, silently pings
`/system/heartbeat` (see `layouts/app.blade.php` and
`SystemController::heartbeat()`) right away and then every 3 minutes for as
long as that page stays open, and that ping is what actually runs the three
commands above — as long as at least one person has the CRM open in a
browser tab, they keep running on their normal schedule. Each command is
still throttled to its own interval (a rapid string of pings from several
open tabs won't run it more than once per window), and `contacts:sync` still
separately respects the interval/enabled setting configured on the Contacts
page. The only gap: if nobody has any page open at all (e.g. overnight),
nothing runs until someone opens one again — the manual "Sync Now" buttons
always work regardless.

If you *do* have cron access and want these running even with no browser
open, they're also registered in `routes/console.php` via the Laravel
scheduler as a drop-in alternative/backup — point a single system cron entry
at it and both mechanisms work fine together:

```bash
* * * * * cd /path-to-app && php artisan schedule:run >> /dev/null 2>&1
```

## Key modules

- **Dashboard** — live stats, weekly volume chart, activity feed.
- **Gmail Inbox** — folders, threaded conversations, reply-in-thread, star/
  archive, and "Create Contact" from an unmatched sender. Backed by a real
  connected Gmail account once set up (see "Gmail Integration" above);
  falls back to local-only storage if nothing is connected.
- **Contacts** — one row per quotation (Quote No.), per-field custom filters
  (including WhatsApp and email), date-range filter, sortable columns
  (including Email and Phone/WhatsApp), star-to-pin-to-top, Active/Follow-up/
  Inactive status automation from the quotation date, manual Archive and
  Won actions (Won/Archived leads get their own tabs and drop out of the
  main pipeline), Excel import/export, and the auto-sync data source
  described above. A lead you archive or delete is never re-added by a
  later sync/import, even if it's still in the source sheet.
- **WhatsApp** — one default template (managed from Settings, admin-only)
  with `{name}` `{company}` `{employee}` `{date}` placeholders. The
  WhatsApp button next to any contact opens a real `wa.me` click-to-chat
  link pre-filled with that template — no picker, one click.
- **Product Master** — Excel import/export, and a "Standard Copper
  Conductor Reference" quick-lookup popup (editable) next to the product
  table.
- **Settings** — profile, password, theme, real Gmail OAuth connect/
  disconnect/sync, WhatsApp template + default, system preferences, and
  (admin-only) Team Accounts management.
- **Access control** — Admin accounts get full access; User accounts get
  day-to-day CRM access (contacts, WhatsApp, Gmail replies) but not
  Settings integrations, Excel import, data-source config, or account
  management.

## Caching

Rarely-changing/reference data is cached (`Cache::rememberForever`, forgotten
on write — never a fixed TTL, so nothing goes stale on its own): the shared
Gmail OAuth Client (`app/Models/GmailAccount.php`), company + personal
WhatsApp templates (`app/Models/WhatsappTemplate.php`), and the Product
Master copper reference tables (`app/Models/ReferenceTable.php`). Everything
transactional — leads, email/WhatsApp history, activities, reminders,
dashboard stats — is intentionally **never** cached, so it's always current.
Each cached model exposes its own `cached...()` read method and
`forget...Cache()`/`forgetCache()` invalidation method; every write path that
touches that data calls the matching `forget` right after saving.
