# Deploying to crm.copperfitting.in

This subdomain is deployed over FTP (`.github/workflows/deploy-copperfitting.yml`,
triggered manually from the Actions tab), because this hosting account has no
SSH access. FTP can only move files, so one manual one-time setup step is
needed on the server side.

## 1. Run the deploy workflow

GitHub → **Actions** tab → **Deploy to crm.copperfitting.in (FTP)** → **Run workflow**.

- First run: leave **dry_run** checked (the default). This only lists what
  *would* be uploaded -- nothing is transferred. Check the step logs and
  confirm every path printed starts with `public_html/crm.copperfitting.in/`
  and nothing else.
- Once that looks right, run it again with **dry_run** unchecked to actually
  upload.

## 2. Set up the cron job (one-time)

FTP can't run commands on the server, so a cron job takes the place of the
`artisan migrate` / cache-rebuild step the SSH-based Hostinger deploy does
automatically. In the hosting panel's **Cron Jobs** section, add:

```
*/15 * * * * php /home/u829428207/public_html/crm.copperfitting.in/app/artisan app:deploy-finalize >> /home/u829428207/crm-deploy-finalize.log 2>&1
```

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
