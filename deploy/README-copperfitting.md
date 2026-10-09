# Deploying to crm.copperfitting.in

This subdomain is deployed over FTP, because this hosting account has no SSH,
panel, or terminal access available to anyone on this -- the FTP account
(`u829428207.orbit`) is the only capability. Its root (`/`) turned out to
already *be* the subdomain's real document root (confirmed by finding
Hostinger's own placeholder `default.php` sitting there) -- not nested under
`public_html/`, despite that being the first guess. `CLIENT_FTP_PATH` is set
to `.` accordingly.

## 1. Get the files up

Either:

- **GitHub Actions workflow**: Actions tab → **Deploy to crm.copperfitting.in
  (FTP)** → **Run workflow**. Leave **dry_run** checked first, check the log,
  then run again with it unchecked. `vendor/` alone is ~25,000 files, so the
  very first full run can take a long time (seen as long as ~70 minutes) --
  the workflow allows up to 90 minutes for it. Later runs are much faster
  since only changed files transfer.
- **Or manually via FileZilla**: Actions tab → **Build crm.copperfitting.in
  bundle (for manual FTP upload)** → Run workflow → download the resulting
  artifact from the run's summary page once it finishes → extract it →
  upload everything *inside* the extracted folder (`app/`, `index.php`,
  `.htaccess`, `favicon.ico`, `robots.txt`) directly into the FTP root. This
  avoids the ~25,000-small-files slowness, but still uploads them
  individually, which can itself take a long time depending on the
  connection -- if FileZilla reports failed transfers at the end, use
  "Reset and requeue selected files" on just the failed ones rather than
  starting over.

Either way, never upload the *containing* folder itself (e.g. a
`crm-copperfitting-bundle` wrapper) -- only its contents, straight into `/`.

## 2. Finish installation (no SSH, no cron, no terminal)

FTP alone can't run `artisan` commands, and if nobody involved has
panel/cron access either, the fallback is `deploy-finalize.php` --
a small, token-protected script that runs `app:deploy-finalize`
(bootstraps `.env` with a generated app key, runs pending migrations,
rebuilds caches) when visited in a browser. It's already uploaded to the FTP
root by both methods above. To use it:

1. Make up a random secret string (anything long and hard to guess).
2. Create a plain text file containing just that string, nothing else, and
   upload it via FTP to `app/storage/app/deploy_token.txt` (that directory
   already exists once the app bundle is uploaded).
3. Visit `https://crm.copperfitting.in/deploy-finalize.php?token=YOUR_SECRET`
   in a browser. First run creates `.env` and stops there (it won't attempt
   migrations before a database is configured) -- you should see something
   like "Application key set successfully" in the plain-text response.
4. Visit `https://crm.copperfitting.in/setup` and run the Setup Wizard --
   enter the database credentials for this subdomain's own MySQL database
   (create one in the hosting panel first, separate from the other sites on
   this account) and create the first admin account. This step runs
   migrations itself, as part of the wizard.
5. For any *future* code deploy that adds new migrations, re-upload the
   changed files (step 1) then visit the `deploy-finalize.php` URL again to
   run them -- it's idempotent and safe to run repeatedly.
6. Once everything works, delete `deploy-finalize.php` and
   `app/storage/app/deploy_token.txt` via FTP. Leaving them is low-risk
   (token-gated, and the underlying command is idempotent) but removing them
   is better hygiene.

If panel/cron access ever does become available, a cron job calling
`php app/artisan app:deploy-finalize` on a schedule is a cleaner long-term
alternative to re-visiting the URL by hand after every deploy -- see the
`app:deploy-finalize` command's own docblock for the exact command shape.

## Notes

- This account also hosts other live sites, including the main
  `copperfitting.in` WordPress site. The deploy workflow only ever writes
  under `CLIENT_FTP_PATH` and never deletes remote files, so it can't affect
  those other sites -- but double-check `CLIENT_FTP_PATH` before ever
  changing it, since a wrong value could in principle point somewhere it
  shouldn't.
- `.env` is never touched by the deploy workflow once it exists -- only
  `app:deploy-finalize` (via cron or `deploy-finalize.php`) creates it, and
  only if it's missing.
