# Deploying SafeNote to Railway

SafeNote ships a `Dockerfile`, so Railway builds and runs it without further
configuration. The same image runs anywhere Docker runs.

## 1. Create the project

1. Sign in to [railway.app](https://railway.app) with the GitHub account that
   holds this repository.
2. **New Project → Deploy from GitHub repo → safenote**.
3. In the same project, **New → Database → Add MySQL**.

Railway detects the `Dockerfile` and builds from it. The first build fails until
the variables below are set, which is expected.

## 2. Generate the application key

`APP_KEY` is what decrypts every counselling record. Generate it on your own
machine and copy the value:

```bash
php artisan key:generate --show
```

Keep a copy somewhere safe and separate from the database. If this key is lost,
every record already written becomes permanently unreadable.

## 3. Set the variables

On the SafeNote service, under **Variables**:

| Variable | Value |
| --- | --- |
| `APP_NAME` | SafeNote |
| `APP_ENV` | production |
| `APP_KEY` | the `base64:...` value from step 2 |
| `APP_DEBUG` | false |
| `APP_URL` | the public URL Railway gives the service |
| `DB_CONNECTION` | mysql |
| `DB_HOST` | `${{MySQL.MYSQLHOST}}` |
| `DB_PORT` | `${{MySQL.MYSQLPORT}}` |
| `DB_DATABASE` | `${{MySQL.MYSQLDATABASE}}` |
| `DB_USERNAME` | `${{MySQL.MYSQLUSER}}` |
| `DB_PASSWORD` | `${{MySQL.MYSQLPASSWORD}}` |
| `SESSION_DRIVER` | database |
| `SESSION_SECURE_COOKIE` | true |
| `SESSION_TIMEOUT_MINUTES` | 15 |
| `LOG_CHANNEL` | stderr |
| `SEED_DEMO_DATA` | true — **for the first deploy only** |

The `${{MySQL.*}}` form is Railway's own reference syntax; it fills in the
database credentials for you.

`APP_DEBUG` must stay `false`. With it on, an error page shows the stack trace,
the configuration and parts of the environment to whoever triggered it.

Add the `SCHOOL_SUPPORT_*` variables from `.env.example` too, so the password
help page names a real person to contact.

## 4. First deploy

The container migrates the database on every boot and, while `SEED_DEMO_DATA` is
`true`, seeds the demo accounts and caseload.

**Delete `SEED_DEMO_DATA` straight after the first successful deploy.** Leaving it
set re-seeds on every restart.

## 5. Close the demo accounts

The seeded accounts use the password `password`, and this repository is public, so
anyone who finds the URL can sign in. Before showing the site to anyone:

1. Sign in as `admin@safenote.test`.
2. Open each account under **Users** and issue a new password for it.
3. Sign in as each one and set a password of your own when SafeNote asks.

Change the admin's own password from **Profile → Update Password**.

## 6. Afterwards

Every push to the default branch rebuilds and redeploys. Railway's own logs show
the build, the migration output and any runtime error.

## What must not go on this deployment

Real counselling records are personal data under the PDPA 2010, and a hosting
account that is free or student-grade is not where a school's confidential student
information belongs. Keep the live site to invented demonstration data. The
version that holds real records belongs on infrastructure the school controls,
with a backup of `APP_KEY` held separately from the database.
