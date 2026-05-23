# Backend Audit Report - Parque Ecologico Itaquaquecetuba

Date: 2026-05-23

## Scope

Full backend audit of the PHP application for Parque Ecologico Itaquaquecetuba, covering routing, controllers, models, database access, admin APIs, public reservation flows, technical visits, authentication, contact messages, blocked dates, validation, error handling, security posture, and deployment readiness.

The final pass intentionally preserved the existing architecture, folder structure, routing style, naming conventions, and implementation patterns. Only precise fixes for real reliability, validation, and production-readiness issues were applied.

## Issues Found

### Critical/High Issues Previously Found And Fixed

- Global CORS accepted any origin while the application uses PHP sessions/cookies.
- Database connection errors exposed raw PDO details to clients.
- Database connection used `utf8` instead of `utf8mb4`.
- Router did not return proper 405 responses for method mismatches.
- Router did not centrally log unhandled controller exceptions.
- Admin technical-visit actions called kiosk reservation endpoints.
- Contact-message delete action used an incompatible route/method from the shared admin helper.
- Technical visits had no complete admin approve/reject/delete API routes.
- Technical visits did not persist or update `status` consistently.
- Kiosk reservation conflict validation was race-prone because validation happened before insert without transactional locking.
- Technical-visit conflict validation had the same race condition.
- Admin approval could create conflicts if data changed after initial request.
- Kiosk availability had to be enforced by same kiosk/date/time only. Different kiosks may be booked at the same time.
- Technical visits accepted inactive/deleted guide IDs.
- Technical visits had weaker operating-hour validation than kiosk reservations.
- Public write endpoints had no rate limiting.
- Login/registration handlers had no brute-force throttling.
- Several public controllers could return raw exception messages.
- Contact status parsing treated string `"false"` as truthy.
- Some delete endpoints returned success for nonexistent records.
- Guide deletion allowed removing guides with future scheduled visits.
- Blocked-date generation accepted unreasonable years.
- Blocked-date population script was hard-coded to 2026.
- Apache did not explicitly deny access to `.env`, SQL, and markdown files.
- Admin UI referenced technical-visit purpose fields without safe fallback.
- The project had a duplicate database dump outside the database directory.
- The project had no favicon asset.

### Final Audit Issues Found And Fixed

- Public/admin controllers accepted non-scalar JSON values in string fields, creating possible `trim()`/`preg_replace()` TypeErrors and 500 responses.
- `AuthController` accepted array passwords, creating possible password-validation TypeErrors.
- `BloqueioController::criar()` assumed decoded JSON was always an object/array.
- `BloqueioController::bloquearTodos()` only read form data, while other admin calls may send JSON.
- `Bloqueio` deletion reported success even when no row was deleted.
- `VisitaTecnicaController::atualizarStatus()` accepted arbitrary status values.
- `GuiaController` allowed invalid phone values for guides.
- Two local development router helper files were left untracked outside the project pattern and could confuse deployment/package contents.

## Files Modified

- `.htaccess`
- `README.md`
- `API_CONTRACT.md`
- `DEPLOY_INFINITYFREE.md`
- `parque_ecologico/.env.example`
- `parque_ecologico/AUDIT_REPORT_BACKEND.md`
- `parque_ecologico/index.php`
- `parque_ecologico/config/database.php`
- `parque_ecologico/app/core/Router.php`
- `parque_ecologico/app/middlewares/AuthMiddleware.php`
- `parque_ecologico/app/helpers/rate_limit.php`
- `parque_ecologico/app/helpers/validation.php`
- `parque_ecologico/app/controllers/AuthController.php`
- `parque_ecologico/app/controllers/AgendamentoController.php`
- `parque_ecologico/app/controllers/VisitaTecnicaController.php`
- `parque_ecologico/app/controllers/ContatoController.php`
- `parque_ecologico/app/controllers/BloqueioController.php`
- `parque_ecologico/app/controllers/GuiaController.php`
- `parque_ecologico/app/models/Bloqueio.php`
- `parque_ecologico/app/models/VisitaTecnica.php`
- `parque_ecologico/app/models/Mensagem.php`
- `parque_ecologico/app/views/pages/admin.html`
- `parque_ecologico/public/js/admin.js`
- `parque_ecologico/public/images/favicon.ico`
- `parque_ecologico/populate_bloqueios.php`
- `parque_ecologico/migrations/003_backend_hardening.sql`

## Fixes Applied

- Restricted CORS to same-origin/configured origins and kept credential handling safe.
- Hardened database connection charset, environment parsing, and error logging.
- Added route method handling and centralized exception logging.
- Added missing admin APIs for technical visits.
- Added contact delete route compatibility for the existing admin helper.
- Added IP-based rate limiting for login, registration, kiosk reservation, technical visit, and contact submission.
- Added MySQL advisory locks plus transactions around kiosk and technical-visit creation.
- Re-checked conflicts during admin approval.
- Enforced kiosk conflict by same `quiosque_id`, date, and overlapping time only.
- Enforced guide/date/time conflicts for technical visits.
- Added technical-visit guide-active validation and operating-hour validation.
- Centralized and enforced valid email validation.
- Normalized and validated phone numbers as 10 or 11 digits where applicable.
- Replaced unsafe public raw exception responses with generic client responses and server-side logging.
- Added 404 handling for delete/status operations that affect no records.
- Prevented guide deletion when future non-rejected/non-cancelled visits exist.
- Added backend hardening migration for statuses, uniqueness, and query indexes.
- Updated blocked-date script to use the current year dynamically.
- Added Apache deny rules for sensitive project files.
- Added favicon and documented backend contract expectations.
- Removed local-only router helper files from the working tree.
- Hardened controllers against malformed JSON, arrays in scalar fields, and invalid status values.

## Remaining Risks

- `Usuario.php` appears to be legacy/dead code and does not match the active authentication schema. It is not routed by the current backend, so it was left untouched to avoid unnecessary architecture changes.
- `PagesController::cadastro()` and `AuthController::register()` appear inactive in the current routing flow. Public self-registration is not exposed, which is safer for now.
- Some query methods still use `SELECT *`. This is acceptable for the current small admin/public screens but should be narrowed if data volume grows.
- `APP_URL` is read from the process environment in `index.php`; if a host does not expose `.env` values as environment variables, same-origin requests still work, but explicit cross-origin configuration may need server-level setup.
- File-based rate limiting depends on `sys_get_temp_dir()` being writable on the host.
- There is no admin audit-log table for approve/reject/delete actions.
- There is no automated migration runner; SQL must be applied carefully through hosting tooling or a controlled deploy process.
- The base schema should always be compared against production before applying future migrations.

## Undefined Business Rules

- Public cancellation policy is not defined. Safest current behavior: cancellation/deletion remains admin-only.
- Holiday/event closure policy depends on records in `bloqueios`; the seed script covers common Brazilian holidays but does not replace administrative review.
- Kiosk inventory is represented by numeric IDs 1-20 because there is no dedicated kiosk table.
- Maximum daily reservation limits per person/user are not fully specified beyond conflict and availability rules.
- Technical-visit maximum capacity is stored/validated by the current form and schema expectations, but detailed institutional capacity policy should be documented.

## Security Evaluation

The backend is significantly safer after the hardening work:

- Admin routes are session protected.
- Non-GET admin mutations use CSRF validation.
- Public write endpoints and auth endpoints have rate limiting.
- Email and phone inputs are validated.
- Malformed JSON and array payloads no longer crash string sanitizers.
- Sensitive files are protected by Apache rules and `.env` is ignored by Git.
- SQL queries use prepared statements.
- Raw exception exposure to public clients was reduced.

Remaining security improvements should include audit logging, stronger production session cookie settings over HTTPS, deployment-level security headers, and host-level backup/restore procedures.

## Performance Evaluation

Current performance is acceptable for municipal reservation volume:

- Reservation conflict checks use indexed columns from the hardening migration.
- Advisory locks avoid race conditions without requiring broad table locks.
- Public/admin queries are simple and bounded by current screen needs.

Future improvements should add pagination to larger admin lists, narrow `SELECT *` queries, and monitor slow queries once real production volume exists.

## Code Cleanliness Evaluation

The codebase is now cleaner and more defensive without changing its architecture:

- Controllers follow the existing style.
- Validation is more consistent.
- Error handling is safer.
- Dead/untracked dev artifacts were removed.
- Known legacy code was documented instead of refactored unnecessarily.

The main cleanliness debt is legacy/inactive auth/user code that should be removed only after confirming it has no planned use.

## How To Test Each Fix

- Syntax check:
  `find parque_ecologico -name '*.php' -exec php -l {} \;`
- Auth check contract:
  `curl -i http://localhost:8000/parque_ecologico/api/auth/check`
  Expected: HTTP 401 when unauthenticated.
- Invalid login payload:
  send JSON with array values for `usuario`/`senha`.
  Expected: HTTP 400, no PHP fatal error.
- Invalid contact payload:
  send JSON with array values for scalar fields.
  Expected: HTTP 422/400 validation response, no PHP fatal error.
- Invalid email:
  submit reservation/contact/visit with malformed email.
  Expected: validation error.
- Same-kiosk conflict:
  submit two overlapping reservations for the same kiosk/date/time.
  Expected: conflict rejection.
- Different-kiosk same time:
  submit overlapping reservations for different kiosk IDs.
  Expected: allowed if all other rules pass.
- Technical visit conflict:
  submit overlapping visits for the same guide/date/time.
  Expected: conflict rejection.
- Invalid visit status:
  call admin status update with a value other than `aprovado` or `rejeitado`.
  Expected: HTTP 400.
- Delete nonexistent bloqueio:
  call bloqueio delete for a nonexistent ID.
  Expected: HTTP 404.
- Guide phone:
  create/update guide with invalid phone length.
  Expected: validation error.

## Commands To Run Locally

- Start local PHP server from repository root:
  `/Applications/XAMPP/xamppfiles/bin/php -S localhost:8000`
- Open the application:
  `http://localhost:8000/parque_ecologico/`
- Import database manually through MySQL/phpMyAdmin:
  `parque_ecologico/database/ParqueEco_banco.sql`
- Apply hardening migration after verifying production data:
  `parque_ecologico/migrations/003_backend_hardening.sql`
- Seed/update blocked dates:
  `cd parque_ecologico && php populate_bloqueios.php`

## Production Readiness Evaluation

Production readiness: strong for a small municipal deployment after the fixes, with some governance items still pending.

The backend now has safer authentication handling, protected admin routes, CSRF on admin mutations, validated public inputs, safer database access, race-condition protection for booking conflicts, consistent visit/reservation business rules, defensive handling for malformed payloads, and deployment documentation.

Before declaring full government-grade production readiness, the city/team should verify the production schema, apply migrations with backup, document final business policies, enable HTTPS-only session cookies, add admin audit logs, and perform a hosted staging test on InfinityFree with real PHP/MySQL settings.
