# Deploying to crm.copperfitting.in

This subdomain is deployed over FTP, because this hosting account has no SSH
access. There are two ways to get the files up there -- pick whichever's
easier. Either way, one manual one-time setup step is needed on the server
side afterward (the cron job, step 2 below).

## 1a. Run the GitHub Actions deploy workflow

GitHub → **Actions** tab → **Deploy to crm.copperfitting.in (FTP)** → **Run workflow**.

- First run: leave **dry_run** checked (the default). This only lists what
  *would* be uploaded -- nothing is transferred. Check the step logs and
  confirm every path printed starts with `public_html/crm.copperfitting.in/`
  and nothing else.
- Once that looks right, run it again with **dry_run** unchecked to actually
  upload.

Note: `vendor/` alone is ~25,000 files across hundreds of directories, and
this host takes roughly 1-1.5s per directory listing, so the very first full
run can take a while (the workflow allows up to 90 minutes for it). Every
run after that is much faster, since it only transfers what changed.

## 1b. Or upload a pre-built zip manually (faster for the first deploy)

Since the file-by-file sync above is slow the first time, it's often faster
to upload one big zip instead of ~25,000 small files individually. Using
your own FTP client (FileZilla, Cyberduck, etc.):

1. Create `public_html/crm.copperfitting.in/app/` if it doesn't exist yet.
2. Upload `app-bundle.zip` into `public_html/crm.copperfitting.in/app/app-bundle.zip`.
3. Upload `deploy/extract-bundle.php` into `public_html/crm.copperfitting.in/app/extract-bundle.php`.
4. Upload `deploy/app-htaccess` into `public_html/crm.copperfitting.in/app/.htaccess`.
5. Upload `deploy/hostinger-index.php` into `public_html/crm.copperfitting.in/index.php`
   (renamed to `index.php` -- note it's going in the *parent* folder, not `app/`).
6. Upload `public-assets.zip`'s contents (`.htaccess`, `favicon.ico`,
   `robots.txt`) directly into `public_html/crm.copperfitting.in/` (the
   same parent folder as index.php -- these are tiny, no extraction needed).

The zip itself doesn't get extracted by your FTP client -- that happens on
the server the next time `extract-bundle.php` runs, which the cron job
below takes care of (it runs the extractor before `app:deploy-finalize` on
every tick, and it's a no-op once there's no zip left waiting).

## 2. Set up the cron job (one-time)

FTP can't run commands on the server, so a cron job takes the place of the
`artisan migrate` / cache-rebuild step the SSH-based Hostinger deploy does
automatically -- and, if you used the manual zip upload (1b), the same cron
job also extracts app-bundle.zip once it sees it. In the hosting panel's
**Cron Jobs** section, add:

```
*/15 * * * * php /home/u829428207/public_html/crm.copperfitting.in/app/extract-bundle.php >> /home/u829428207/crm-deploy-finalize.log 2>&1 && php /home/u829428207/public_html/crm.copperfitting.in/app/artisan app:deploy-finalize >> /home/u829428207/crm-deploy-finalize.log 2>&1
```

(If you only used the GitHub Actions workflow (1a) and never upload a zip,
`extract-bundle.php` just prints "nothing to extract" and exits -- harmless
either way, so this one cron line covers both deploy methods.)

Adjust:
- The `php` binary -- the panel's Cron Jobs page usually has a dropdown to
  pick the PHP version/path for that account; use PHP 8.2+. If it doesn't,
  check **PHP Configuration** in the panel for the actual binary path
  (something like `/opt/alt/php82/usr/bin/php`).
- The full path before `artisan` -- confirm it matches where the workflow
  actually uploaded the app bundle (`CLIENT_FTP_PATH/app`).

Every 15 minutes is a reasonable default (the command is a safe no-op when
there's nothing new to do), or use the panel's "run now" option if it has
one, right after a deploy.

## 3. Finish installation

Once the cron job has run at least once (creating `.env` with a generated
app key), visit `https://crm.copperfitting.in/setup` to run the Setup
Wizard -- enter the database credentials for this subdomain's own MySQL
database (create one in the hosting panel first, separate from the other
sites on this account) and create the first admin account.

## Notes

- This account also hosts other live sites, including the main
  `copperfitting.in` WordPress site. The deploy workflow only ever writes
  under `CLIENT_FTP_PATH` (`public_html/crm.copperfitting.in`) and never
  deletes remote files, so it can't affect those other sites.
- `.env` is never touched by the deploy workflow once it exists -- only
  `app:deploy-finalize` creates it, and only if it's missing.
