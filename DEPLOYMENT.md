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

**a. Confirm SSH access and get your connection details**
hPanel → Advanced → SSH Access. Note the hostname, port (usually `65002`
on Hostinger shared hosting), and username.

**b. Generate a deploy keypair** (on your own machine, not on Hostinger):

```bash
ssh-keygen -t ed25519 -C "github-actions-deploy" -f hostinger_deploy_key -N ""
```

This makes two files: `hostinger_deploy_key` (private) and
`hostinger_deploy_key.pub` (public).

**c. Add the public key to Hostinger.** Either paste the contents of
`hostinger_deploy_key.pub` into hPanel's SSH Access → Manage SSH Keys, or
SSH in with your normal credentials once and run:

```bash
mkdir -p ~/.ssh && chmod 700 ~/.ssh
echo "PASTE_PUBLIC_KEY_CONTENTS_HERE" >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
```

**d. Pick where the app lives**, e.g. `~/laravel-app` (outside
`public_html` — Laravel's `app/`, `config/`, `.env` etc. must never be
web-accessible). Create it:

```bash
mkdir -p ~/laravel-app
```

**e. Point your domain's document root at `laravel-app/public`.** In
hPanel → Websites → (your domain) → look for a "Document root" / website
folder setting and point it at `laravel-app/public`. If your plan doesn't
allow changing the document root, use this fallback instead:

```bash
rm -rf ~/domains/yourdomain.com/public_html
ln -s ~/laravel-app/public ~/domains/yourdomain.com/public_html
```

**f. Seed the directories the deploy step deliberately never touches**
(so `.env`, uploads, and sessions survive every future deploy):

```bash
cd ~/laravel-app
mkdir -p storage/app/private storage/app/public
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views
mkdir -p storage/logs
chmod -R 775 storage bootstrap/cache
```

**g. Create the MySQL database** in hPanel → Databases → MySQL Databases.
Note the database name, username, and password (Hostinger MySQL host is
usually `localhost` from within the same account).

**h. Create `~/laravel-app/.env` by hand, once**, with at minimum:

```
APP_NAME="Vivek Jain CRM"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com
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
`~/laravel-app` over SSH — check `php -v` first; if it's not 8.2+, use
hPanel's PHP version selector for this domain, and you may need to invoke
`php8.2` explicitly instead of `php` in these commands).

> You will **not** need to visit `/setup` on this server — steps g/h above
> do what the Setup Wizard does, so `EnsureAppIsInstalled` needs a marker
> file too: `touch storage/app/installed.lock`.

**i. Add the cron job** that drives the follow-up automation and
Excel/Sheets sync. hPanel → Advanced → Cron Jobs:

```
* * * * * php /home/YOUR_USERNAME/laravel-app/artisan schedule:run >> /dev/null 2>&1
```

## 2. One-time GitHub setup

Repo → Settings → Secrets and variables → Actions → New repository secret.
Add all of these:

| Secret | Value |
|---|---|
| `HOSTINGER_HOST` | SSH hostname from hPanel (e.g. `srv123.hostinger.com`) |
| `HOSTINGER_PORT` | SSH port from hPanel (e.g. `65002`) |
| `HOSTINGER_USERNAME` | SSH username from hPanel |
| `HOSTINGER_SSH_KEY` | Full contents of `hostinger_deploy_key` (the **private** key file) |
| `HOSTINGER_DEPLOY_PATH` | Absolute path to the app, e.g. `/home/username/laravel-app/` (trailing slash matters for rsync) |

Delete the local `hostinger_deploy_key` / `.pub` files once they're saved
as a secret — you won't need them again.

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
