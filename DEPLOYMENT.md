# Deploying to Hostinger

Every push to `main` triggers `.github/workflows/deploy.yml`, which:

1. Builds the app (Composer, production dependencies) on GitHub's runner —
   **not** on the shared-hosting box, since Hostinger shared plans are too
   resource-constrained to reliably run `composer install` themselves.
2. Rsyncs the app code (vendor, app, bootstrap, config, etc.) into
   `HOSTINGER_DEPLOY_PATH/app/` over SSH — a subfolder *inside* your public_html, not a
   sibling directory. This hosting plan has no way to point the document
   root somewhere else or reliably follow a symlink out of it (shared-hosting
   PHP-FPM sandboxing tends to block that silently), so everything has to
   live under the one folder Hostinger actually serves.
3. Rsyncs `public/`'s contents (CSS/JS/images, `.htaccess`, favicon,
   robots.txt) directly into `HOSTINGER_DEPLOY_PATH` itself, and deploys a modified
   `index.php` (`deploy/hostinger-index.php`) that points into `app/` instead
   of the stock `../vendor`, `../bootstrap` paths.
4. Deploys `app/.htaccess` (`Require all denied`) so `HOSTINGER_DEPLOY_PATH/app/` —
   which holds `.env`, `vendor/`, everything — is never directly reachable
   over HTTP, even though it's nested under the same webroot as `index.php`.
5. Creates the storage directories Laravel needs to write into, and
   bootstraps `.env` from `.env.example` the first time only (subsequent
   deploys leave a real `.env` alone).
6. Runs `migrate`, cache-warms config/routes/views, and restarts the queue —
   but only once `storage/app/installed.lock` exists, i.e. only after you've
   completed the Setup Wizard below. Before that, this step just prints a
   reminder and exits successfully.

None of your credentials live in this repo or in chat — everything
account-specific is a GitHub Actions secret you set up yourself, once.

## 1. Things only you can do (hPanel UI, not automatable)

**a. Bump PHP to 8.2+** — hPanel → Advanced → PHP Configuration → select
8.2 or newer for your domain. Hostinger accounts commonly default to an
older PHP version, which Laravel 11 can't run on. (The pipeline's SSH
commands separately hunt for a versioned 8.2+ CLI binary since the CLI
alias and the web-facing PHP-FPM version are configured independently —
but the actual page requests still go through whatever PHP-FPM version is
set here, so this step matters regardless.)

**b. Create the MySQL database** — hPanel → Databases → MySQL Databases.
Note the database name, username, and password; you'll type these into
the Setup Wizard (next section), not into a file.

**c. Add the cron job** that drives the follow-up automation and
Excel/Sheets sync — hPanel → Advanced → Cron Jobs:

```
* * * * * php /path/to/your/public_html/app/artisan schedule:run >> /dev/null 2>&1
```

(If the bare `php` there turns out to be the wrong version too, hPanel's
Cron Jobs form sometimes has its own PHP-version dropdown — check that
before switching to a versioned binary path.)

## 2. Things the pipeline does for you automatically

- Rsyncs the app into `HOSTINGER_DEPLOY_PATH/app/` and the built public assets +
  front controller into `HOSTINGER_DEPLOY_PATH` itself.
- Locks down `HOSTINGER_DEPLOY_PATH/app/` from direct web access.
- Creates `storage/app/{private,public}`, `storage/framework/{cache/data,sessions,views}`,
  `storage/logs`, and `chmod 775`s them.
- Bootstraps `.env` (and `APP_KEY`) the first time there isn't one.

You don't need to run any of this by hand or over SSH yourself.

## 3. Finish install via the Setup Wizard

Once 1a and 1b above are done (PHP bumped, database created) and at least
one deploy has run, visit:

**https://your-domain.example/setup**

Enter the MySQL credentials from step 1b, then create your admin account.
This writes `.env`, runs migrations, and creates
`storage/app/installed.lock` — which is what tells the deploy pipeline
it's safe to start running `migrate`/cache commands on future deploys.

## 4. One-time GitHub setup

Repo → Settings → Secrets and variables → Actions → New repository secret.
Add all of these:

| Secret | Value |
|---|---|
| `HOSTINGER_HOST` | Your Hostinger SSH host/IP (hPanel → SSH Access) |
| `HOSTINGER_PORT` | Your Hostinger SSH port (hPanel → SSH Access) |
| `HOSTINGER_USERNAME` | Your Hostinger SSH username (hPanel → SSH Access) |
| `HOSTINGER_PASSWORD` | Your Hostinger SSH password (hPanel → SSH Access → Password → Change, if you need to (re)set it) |
| `HOSTINGER_DEPLOY_PATH` | The absolute path to your domain's `public_html` folder, e.g. `/home/u123456789/domains/yourdomain.com/public_html` (no trailing slash) |

That's it — five secrets, no SSH key setup. `HOSTINGER_PUBLIC_HTML_PATH` from
an earlier symlink-based setup is no longer read by the workflow and can be
deleted whenever convenient.

Using a password instead of an SSH key is simpler to wire up, but it means
this exact password — your real Hostinger login — lives in GitHub Secrets.
If you'd rather scope this down to a revocable deploy-only credential
later, switching back to key-based auth just means changing this workflow;
nothing else about the setup changes.

## 5. Going forward

Push to `main` → GitHub Actions builds and deploys automatically. Watch
progress under the repo's **Actions** tab.

To deploy without a new commit (e.g. after only changing a Hostinger-side
setting), use **Actions → Deploy to Hostinger → Run workflow**.

## 6. Rollback

There's no automatic rollback — revert the bad commit on `main` and push;
that triggers a fresh deploy of the reverted state. Because `.env` and
`storage/` are excluded from sync, rolling back code never touches your
live database or uploaded files.
