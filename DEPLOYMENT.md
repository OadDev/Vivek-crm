# Deploying to Hostinger

Every push to `main` triggers `.github/workflows/deploy.yml`, which:

1. Builds the app (Composer, production dependencies) on GitHub's runner —
   **not** on the shared-hosting box, since Hostinger shared plans are too
   resource-constrained to reliably run `composer install` themselves.
2. Rsyncs the built app to your Hostinger account over SSH.
3. SSHes in again to run `migrate`, cache-warm the config/routes/views, and
   restart the queue.

None of your credentials live in this repo or in chat — everything
account-specific is a GitHub Actions secret you set up yourself, once.

## 1. One-time Hostinger setup

Do this in hPanel and over SSH, before the first deploy.

**a. SSH connection details** (hPanel → Advanced → SSH Access):

| | Value |
|---|---|
| IP | `217.21.81.23` |
| Port | `65002` |
| Username | `u476218181` |
| Password | whatever's set under "Password → Change" on that page |

This deploy uses password auth (simpler to set up than an SSH key, at the
cost of your actual Hostinger account password living in GitHub Secrets —
see the note in step 2). No public key needs adding anywhere.

**b. Pick where the app lives**: `/home/u476218181/laravel-app` (outside
`public_html` — Laravel's `app/`, `config/`, `.env` etc. must never be
web-accessible). Create it:

```bash
mkdir -p /home/u476218181/laravel-app
```

**c. Point `lightskyblue-snail-995478.hostingersite.com`'s document root at
`laravel-app/public`.** In hPanel → Websites → that site → look for a
"Document root" / website folder setting. If your plan doesn't allow
changing it, use this fallback instead:

```bash
rm -rf /home/u476218181/public_html
ln -s /home/u476218181/laravel-app/public /home/u476218181/public_html
```

**d. Seed the directories the deploy step deliberately never touches**
(so `.env`, uploads, and sessions survive every future deploy):

```bash
cd /home/u476218181/laravel-app
mkdir -p storage/app/private storage/app/public
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views
mkdir -p storage/logs
chmod -R 775 storage bootstrap/cache
```

**e. Create the MySQL database** in hPanel → Databases → MySQL Databases.
Note the database name, username, and password (Hostinger MySQL host is
usually `localhost` from within the same account).

**f. Create `/home/u476218181/laravel-app/.env` by hand, once**, with at
minimum:

```
APP_NAME="Vivek Jain CRM"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://lightskyblue-snail-995478.hostingersite.com
APP_TIMEZONE=Asia/Kolkata

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_hostinger_db_name
DB_USERNAME=your_hostinger_db_user
DB_PASSWORD=your_hostinger_db_password

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

Then generate the app key once: `php artisan key:generate` (run from
`laravel-app` over SSH — check `php -v` first; if it's not 8.2+, use
hPanel's PHP version selector for this domain, and you may need to invoke
`php8.2` explicitly instead of `php` in these commands).

> You will **not** need to visit `/setup` on this server — steps e/f above
> do what the Setup Wizard does, so `EnsureAppIsInstalled` needs a marker
> file too: `touch storage/app/installed.lock`.

**g. Add the cron job** that drives the follow-up automation and
Excel/Sheets sync. hPanel → Advanced → Cron Jobs:

```
* * * * * php /home/u476218181/laravel-app/artisan schedule:run >> /dev/null 2>&1
```

## 2. One-time GitHub setup

Repo → Settings → Secrets and variables → Actions → New repository secret.
Add all of these:

| Secret | Value |
|---|---|
| `HOSTINGER_HOST` | `217.21.81.23` |
| `HOSTINGER_PORT` | `65002` |
| `HOSTINGER_USERNAME` | `u476218181` |
| `HOSTINGER_PASSWORD` | Your Hostinger SSH password (hPanel → SSH Access → Password → Change, if you need to (re)set it) |
| `HOSTINGER_DEPLOY_PATH` | `/home/u476218181/laravel-app/` (trailing slash matters for rsync) |

Using a password instead of an SSH key is simpler to wire up, but it means
this exact password — your real Hostinger login — lives in GitHub Secrets.
If you'd rather scope this down to a revocable deploy-only credential
later, switching back to key-based auth just means changing this workflow
back; nothing else about the setup changes.

## 3. Going forward

Push to `main` → GitHub Actions builds and deploys automatically. Watch
progress under the repo's **Actions** tab. First deploy will fail fast and
tell you exactly which secret or SSH step is wrong — that's expected while
dialing this in.

To deploy without a new commit (e.g. after only changing a Hostinger-side
setting), use **Actions → Deploy to Hostinger → Run workflow**.

## 4. Rollback

There's no automatic rollback — revert the bad commit on `main` and push;
that triggers a fresh deploy of the reverted state. Because `.env` and
`storage/` are excluded from sync, rolling back code never touches your
live database or uploaded files.
