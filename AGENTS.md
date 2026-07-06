# AGENTS.md — OpenCode Session Guide

## Architecture

Pure PHP 8.2 app (no framework). Single-entry-point router in `index.php` dispatches via `$_GET['module']` and `$_GET['action']`. All routing is a flat if/switch chain — no autoloader, no namespaces.

**Stack:** PHP 8.2 + Apache, MySQL 8.0, Caddy reverse proxy (HTTPS), Docker Compose.

**Bilingual:** EN/ES via `Locale::get('key')` with cookie-based switching. Translation keys live in `lang/en.php` and `lang/es.php`. Always add both when adding new strings.

## Key directories

```
controllers/   — One controller per module (Auth, UserController, BagController, etc.)
models/        — Plain PHP classes with direct PDO queries, no ORM
views/         — PHP templates, no templating engine
views/header.php — All modals + JS (~950 lines), includes Auth checks
lang/          — en.php, es.php translation arrays
database/      — schema.sql (stale!) + migration_*.sql files
uploads/       — User/bag pictures, attachments. Has bags/ subdirectory.
config/        — database.php (PDO connection), config.php (app version)
caddy/         — Caddyfile for reverse proxy
```

## Roles (users.role TINYINT)

| Value | Role | Access |
|-------|------|--------|
| 0 | Disabled | Cannot login |
| 1 | Normal | Own payments only |
| 2 | Admin | Full access except group CRUD |
| 3 | Superadmin | Everything + group management |

`Auth::isAdmin()` returns true for roles >= 2. `Auth::isSuperAdmin()` checks role == 3.

## Groups (bags) — Multi-tenancy

Users, payments, activities, expenses, and logs are segmented by `bag_id`. The `bags` table uses backticks in queries because `groups` is a MySQL reserved word — the code intentionally uses "bag" internally but shows "Group" in the UI.

**Superadmin many-to-many:** `bag_user` pivot table. Superadmin belongs to multiple bags, selects one at login. Regular users have a single `bag_id` on the users table.

**Username uniqueness:** `UNIQUE(username, bag_id)` — same username can exist in different bags, but not in the same bag (including soft-deleted users).

## What to change when adding features

1. **New model field:** Add column to SQL, update model `create()`/`update()`, update controller `store()`/`update()`, update view form
2. **New translation:** Add key to BOTH `lang/en.php` and `lang/es.php`
3. **New route:** Add case in `index.php` switch block, add controller method
4. **New modal:** Add to `views/header.php` or the relevant list view — check for duplicate element IDs!
5. **Disabled dropdown styling:** Use `bg-gray-100 text-gray-500 cursor-not-allowed opacity-70` inline styles

## View conventions

**CRUD forms must be modals, not separate views:**
- Create and edit forms should be implemented as modal windows in the list view
- Only list views should be separate `.php` files
- Modals use prefixed element IDs to avoid conflicts (e.g. `user_create_name`, `bag_edit_status`)
- AJAX `get_json` endpoints fetch data for edit modals
- Each list view has its own `<script>` section with modal open/close/fetch logic

**Pattern for adding modal CRUD to a module:**
1. Add create/edit modals HTML to `views/{module}/list.php`
2. Add JavaScript functions: `openCreateModal()`, `closeCreateModal()`, `openEditModal(id)`, `closeEditModal()`
3. Add `getJson($id)` method to controller for AJAX data fetch
4. Add `get_json` case to router in `index.php`
5. Remove `create()` and `edit()` methods from controller
6. Remove `create` and `edit` cases from router
7. Delete old `create.php` and `edit.php` view files

## Element ID naming convention

All element IDs must use model name as prefix to avoid conflicts:
- `bag_name_edit`, `bag_description_edit`, `bag_status_edit`
- `user_name_create`, `user_role_edit`
- `payment_amount_edit`, `payment_date_create`

Never use generic IDs like `edit_name`, `edit_status`, `create_form` — they will collide with other modals in header.php or other list views.

## Gotchas

- **`schema.sql` is stale** — it does NOT contain activities, expenses, activity_logs, bags, bag_user tables, or the username/role/password/multiplier/bag_id columns on users. Migrations must be applied manually.
- **Database migrations are manual** — run SQL files from `database/` against the DB. No migration runner exists.
- **`header.php` is the modal container** — all CRUD modals (payment, expense, profile, password, credentials) live here. Bag and user list modals live in their own list views.
- **Element ID conflicts** — header.php has `edit_status`, `modal_user_id` etc. Use prefixed IDs in list views (e.g. `bag_edit_status`, `user_edit_status`).
- **AJAX get_json endpoints** — must NOT use `Auth::requireAdmin()` in the router (it sends a redirect header). Auth check happens inside the controller method, returns JSON error instead.
- **`strip_tags(null)` deprecation** — always use `strip_tags($val ?? '')` or ternary for nullable fields.
- **Session start after headers** — `Auth::startSession()` uses `headers_sent()` check and `@` suppression for `ini_set` calls.
- **Image processing** — GD + EXIF extensions installed. Bag thumbnails go to `uploads/bags/`, user thumbnails to `uploads/`. Both use 128px 1:1 crop.
- **`groups` is MySQL reserved** — the bags table and all queries use `bag`/`bags` internally. The UI shows "Group/Groups" to users.
- **Superadmin excluded from totals** — weekly plan, dashboard stats, and payment dropdowns filter out `role = 3` users.
- **`groups` is a MySQL reserved word** — the table is named `bags`, all code uses `bag`/`bag_id`, but the UI shows "Group/Groups" to users.
- **Truncate deletes everything** — truncating a bag deletes all related records AND the bag itself. Dump file is created first with all data including the bag record.

## Remote deployment

Remote server: `10.20.30.145`, Docker context named `remote`.

```bash
# Deploy files
docker --context remote cp <local-file> oc-test-php-app-1:/var/www/html/<path>

# Run SQL
docker --context remote exec oc-test-php-db-1 mysql -uroot -proot savings_db -e "SQL HERE"

# Check logs
docker --context remote exec oc-test-php-app-1 cat /var/log/php_errors.log

# Fix ownership (deployed files are root-owned)
docker --context remote exec oc-test-php-app-1 chown -R www-data:www-data /var/www/html/
```

**MySQL access:** Port 59103 exposed on remote host, credentials in `.env` (root/password).

## Commit conventions

**Commits and pushes must ONLY be done under the user's direct request.** Never auto-commit or auto-push after making changes.

When the user requests a commit, every commit must:
1. **Update README.md** — version number in the "Version" section
2. **Update DEPLOYMENT.md** — version number in example `.env` and variable table (if applicable)
3. **Update `config/config.php`** — `app_version` and `app_build_date` (date only, UTC-5 timezone)
4. **Push changes** to remote repository
5. **Deploy to remote server** via Docker context `remote`

## What NOT to do

- Don't commit `.env` or secrets
- Don't commit `AGENTS.md` (in `.gitignore`)
- Don't use `Auth::requireAdmin()` before `get_json` AJAX endpoints in `index.php`
- Don't use `echo ${var}` — PHP 8.2 deprecation, use `{$var}`
- Don't assume `schema.sql` matches the live DB
- Don't forget to add translations to both lang files
- Don't use duplicate HTML element IDs across views + header.php
- Don't create separate `create.php` or `edit.php` views — use modals in list views
- Don't use `strip_tags()` on nullable values without null coalescing (`?? ''`)
- Don't use `data.value || 'default'` in JavaScript for status fields — `0` is falsy, use proper null check
- Don't auto-commit or auto-push — commits and pushes must ONLY be done under the user's direct request
- Don't put real credentials, passwords, usernames, or port numbers in `.env.example` — use placeholders only
- Don't commit `.env` or secrets
- **NEVER upload `.env` to remote server (production)** — this exposes all credentials. The `.env` must only exist locally and on the remote server (manually placed). Only deploy code files, not secrets.
- Don't deploy `.env` files via `docker cp` or any other method to production servers

## Env file rules

- If `.env` is modified, `.env.example` must be updated to match the same structure (new vars, removed vars)
- `.env.example` must NEVER contain real sensitive information: passwords, API keys, SMTP credentials, SSH passwords
- `.env.example` usernames must be placeholders (e.g., `your_db_username`), NOT real values
- `.env.example` port numbers must be placeholders (e.g., `9283`) or example values that differ from `.env`
- `.env.example` domain must be placeholder (e.g., `your-domain.com`), NOT the real domain
- `.env` contains real values; `.env.example` contains structural placeholders only

## Web-accessible folders

Only these folders/files may be accessed from the web browser:
- `index.php` — entry point / router
- `locale.php` — language switching (`?lang=xx`)
- `manifest.json` — PWA manifest
- `sw.js` — service worker
- `favicon.svg` — favicon
- `uploads/*` — user/bag pictures, attachments, PWA icons
- `uploads/dumps/` — **must NOT** be web-accessible (backup files)

All other directories return 404: `config/`, `controllers/`, `models/`, `views/`, `lang/`, `handlers/`, `helpers/`, `database/`, `caddy/`, `docker/`, `app-icons/`, `secrets/`, `local/`, `.git/`
