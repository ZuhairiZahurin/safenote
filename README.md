# SafeNote

A secure counselling record management system for the Unit Bimbingan dan Kaunseling
of SMK Pandan Indah.

Counselling notes are confidential by law and by professional duty, yet the unit
kept them in paper files and shared spreadsheets. SafeNote replaces that with a
system where a record is readable only by the counsellor who wrote it, is
encrypted on disk, and leaves a trail whenever it is opened, printed or changed.

Built as a Final Year Project for the Bachelor of Information Technology (Hons)
in Cyber Security, Universiti Poly-Tech Malaysia (FYP4112).

## What it does

**Counsellor** — writes and searches counselling records, sees caseload statistics,
prints a case file or a caseload report, and works through referrals sent by teachers.

**Teacher** — refers a student to the unit and follows the *status* of that referral.
A teacher never sees the counselling notes that result from it, and only sees the
student profiles of their own class.

**Admin** — creates and deactivates staff accounts, issues passwords, unlocks
accounts and reads the audit log. An Admin cannot read a counselling record.

## How it is kept secure

| Concern | How it is handled |
| --- | --- |
| Confidentiality at rest | Record content is encrypted with AES-256-CBC through Laravel's `encrypted` cast, keyed by `APP_KEY`. The database stores ciphertext only. |
| Who may see what | Role middleware on every route group, plus a record ownership check, so one counsellor cannot open another's records. |
| Accountability | An append-only audit log written by model observers; printing is logged too, since a printed page leaves the system's control. |
| Brute force | Five failed sign-ins lock an account for 15 minutes; 20 attempts a minute from one address are throttled. Failures give one generic message, so no one can discover which addresses hold accounts. |
| Unattended screens | Sessions are ended after 15 minutes of inactivity, and regenerated on sign-in. |
| Forgotten passwords | No reset links are emailed. The Admin identifies the holder in person and issues a password, which the holder must replace before anything else opens. Issuing one also unlocks the account and ends every session still signed in as that user. |

## Running it locally

Requires PHP 8.2 or newer, Composer and Node.

```bash
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```

Then open http://127.0.0.1:8000.

The seeder creates one account per role, each with the password `password`:

| Role | Email |
| --- | --- |
| Admin | admin@safenote.test |
| Counsellor | counsellor@safenote.test |
| Teacher | teacher@safenote.test |

The seeded caseload is invented demo data. No real student information is in this
repository, and none should ever be committed to it.

## Tests

```bash
php artisan test
```

94 feature tests cover encryption at rest, role access control, the class scoping
a teacher is held to, student identity resolution, brute-force lockout, session
timeout, audit logging, referrals, report printing, admin password issuing and the
forced password change.

## Two things to know before deploying

`APP_KEY` decrypts every counselling note. It is not in this repository, and it
must not be. If it is lost or regenerated, every existing record becomes
permanently unreadable — there is no recovery. Back it up separately from the
database, and keep `.env` out of version control.

Real counselling records are personal data under the PDPA 2010. Free or shared
hosting is suitable for a demonstration with invented data only.
