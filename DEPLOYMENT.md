# Deploying to Hostinger

Every push to `main` triggers `.github/workflows/deploy.yml`, which:

1. Builds the app (Composer, production dependencies) on GitHub's runner —
   **not** on the shared-hosting box, since Hostinger shared plans are too
   resource-constrained to reliably run `composer install` themselves.
2. Rsyncs the built app to your Hostinger account over SSH.
3. SSHes in again to create the storage directories and symlink
   `public_html` at this app's `public/` folder (idempotent — safe on
   every deploy).
4. SSHes in a third time to run `migrate`, cache-warm the config/routes/
   views, and restart the queue — but only once `storage/app/installed.lock`
   exists, i.e. only after you've completed the Setup Wizard below. Before
   that, this step just prints a reminder and exits successfully.

None of your credentials live in this repo or in chat — everything
account-specific is a GitHub Actions secret you set up yourself, once.
The pipeline can create directories and symlinks for you (it already has
SSH access via those secrets), but it can't touch hPanel's own UI — so a
couple of things below still need you specifically.

## 1. Things only you can do (hPanel UI, not automatable)

**a. Bump PHP to 8.2+** — hPanel → Advanced → PHP Configuration → select
8.2 or newer for `lightskyblue-snail-995478.hostingersite.com`. This
account defaulted to PHP 8.1.34, which Laravel 11 can't run on.

**b. Create the MySQL database** — hPanel → Databases → MySQL Databases.
Note the database name, username, and password; you'll type these into
the Setup Wizard (next section), not into a file.

**c. Add the cron job** that drives the follow-up automation and
Excel/Sheets sync — hPanel → Advanced → Cron Jobs:

```
* * * * * php /home/u476218181/laravel-app/artisan schedule:run >> /dev/null 2>&1
```

(If the bare `php` there turns out to be the wrong version too, hPanel's
Cron Jobs form sometimes has its own PHP-version dropdown — check that
before switching to a versioned binary path.)

## 2. Things the pipeline now does for you automatically

- Creates `/home/u476218181/laravel-app` and rsyncs the built app into it.
- Creates `storage/app/{private,public}`, `storage/framework/{cache/data,sessions,views}`,
  `storage/logs`, and `chmod 775`s them.
- Replaces `/home/u476218181/domains/lightskyblue-snail-995478.hostingersite.com/public_html`
  with a symlink to `laravel-app/public`, so the domain actually serves
  this app instead of Hostinger's placeholder page.

You don't need to run any of this by hand or over SSH yourself.

## 3. Finish install via the Setup Wizard

Once 1a and 1b above are done (PHP bumped, database created) and at least
one deploy has run (so the symlink from step 2 exists), visit:

**https://lightskyblue-snail-995478.hostingersite.com/setup**

Enter the MySQL credentials from step 1b, then create your admin account.
This writes `.env`, runs migrations, and creates
`storage/app/installed.lock` — which is what tells the deploy pipeline
it's safe to start running `migrate`/cache commands on future deploys.

## 4. One-time GitHub setup

Repo → Settings → Secrets and variables → Actions → New repository secret.
Add all of these:

| Secret | Value |
|---|---|
| `HOSTINGER_HOST` | `217.21.81.23` |
| `HOSTINGER_PORT` | `65002` |
| `HOSTINGER_USERNAME` | `u476218181` |
| `HOSTINGER_PASSWORD` | Your Hostinger SSH password (hPanel → SSH Access → Password → Change, if you need to (re)set it) |
| `HOSTINGER_DEPLOY_PATH` | `/home/u476218181/laravel-app/` (trailing slash matters for rsync) |
| `HOSTINGER_PUBLIC_HTML_PATH` | `/home/u476218181/domains/lightskyblue-snail-995478.hostingersite.com/public_html` (no trailing slash — this gets replaced with a symlink) |

Using a password instead of an SSH key is simpler to wire up, but it means
this exact password — your real Hostinger login — lives in GitHub Secrets.
If you'd rather scope this down to a revocable deploy-only credential
later, switching back to key-based auth just means changing this workflow
back; nothing else about the setup changes.

## 5. Going forward

Push to `main` → GitHub Actions builds and deploys automatically. Watch
progress under the repo's **Actions** tab. First deploy will fail fast and
tell you exactly which secret or SSH step is wrong — that's expected while
dialing this in.

To deploy without a new commit (e.g. after only changing a Hostinger-side
setting), use **Actions → Deploy to Hostinger → Run workflow**.

## 6. Rollback

There's no automatic rollback — revert the bad commit on `main` and push;
that triggers a fresh deploy of the reverted state. Because `.env` and
`storage/` are excluded from sync, rolling back code never touches your
live database or uploaded files.
