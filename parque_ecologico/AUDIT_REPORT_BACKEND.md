# Backend Audit Report - Parque Ecologico Itaquaquecetuba

Date: 2026-05-23

## Scope

Audited PHP backend routing, controllers, models, database access, admin APIs, booking rules, security posture, reliability, and frontend/API contracts for kiosk reservations, technical visits, contact messages, guides, authentication, and blocked dates.

## Issues Detected And Fixed

- Global CORS allowed any origin while using cookie/session authentication.
- Database connection leaked PDO exception details to clients and used `utf8` instead of `utf8mb4`.
- Router returned only 404 for method mismatches and did not log unhandled exceptions.
- Admin technical-visit cards called kiosk reservation endpoints for approve/reject/delete.
- Contact-message cards called the wrong delete endpoint from the shared admin helper.
- Technical visits had no admin approve/reject/delete API routes.
- Technical visits did not persist an explicit `status`, while admin UI expected one.
- Booking conflict checks were race-prone because validation happened before insert without a transactional lock.
- Kiosk and technical-visit conflict validation did not re-check availability during admin approval.
- Kiosk reservations must not overlap for the same kiosk. Different kiosks may be reserved in the same time window.
- Technical visits did not validate operating hours consistently with kiosk reservations.
- Technical visits accepted inactive or deleted guide IDs.
- Public write endpoints had no rate limiting.
- Login and registration handlers had no brute-force throttling.
- Several controllers returned raw exception messages to public clients.
- Contact status parsing treated string `"false"` as truthy.
- Delete endpoints returned success even when records did not exist.
- Guide deletion could remove guides with future visits.
- Blocked-date holiday generation accepted unreasonable years.
- Blocked-date population script was hard-coded to 2026.
- `.env`, SQL, and markdown project files were not explicitly denied by Apache rules.
- Admin view referenced missing `proposito_visita` values without fallback.

## Files Modified

- `.htaccess`
- `index.php`
- `config/database.php`
- `app/core/Router.php`
- `app/middlewares/AuthMiddleware.php`
- `app/helpers/rate_limit.php`
- `app/controllers/AuthController.php`
- `app/controllers/AgendamentoController.php`
- `app/controllers/VisitaTecnicaController.php`
- `app/controllers/ContatoController.php`
- `app/controllers/BloqueioController.php`
- `app/controllers/GuiaController.php`
- `app/models/VisitaTecnica.php`
- `app/models/Mensagem.php`
- `app/views/pages/admin.html`
- `public/js/admin.js`
- `populate_bloqueios.php`
- `migrations/003_backend_hardening.sql`

## Fixes Applied

- Restricted CORS to same host/configured origin and enabled credential-safe origin echoing.
- Added safer database charset, env parsing, and internal logging for connection failures.
- Added 405 handling and central exception logging in the router.
- Added admin routes for `/api/visitas/listar`, `/api/visitas/aprovar/{id}`, `/api/visitas/rejeitar/{id}`, and `/api/visitas/excluir/{id}`.
- Added POST compatibility for `/api/contato/excluir/{id}` used by the admin helper.
- Added file-backed IP rate limiting for login, registration, kiosk reservation, technical visit, and contact submission.
- Added MySQL advisory locks plus transactions around kiosk and visit creation.
- Added conflict re-checks during approval.
- Preserved kiosk availability by `quiosque_id`: overlapping non-rejected reservations are blocked only for the same kiosk.
- Added visit status persistence and admin status updates.
- Added technical-visit guide-active validation and 08:00-16:00 operating-hours validation.
- Normalized and validated phone numbers as 10 or 11 digits.
- Replaced public raw exception responses with generic messages and server-side `error_log`.
- Added not-found handling for delete/status operations.
- Prevented deleting guides with future non-rejected visits.
- Added hardening migration for statuses, uniqueness, and performance indexes.
- Updated blocked-date seed script to use the current year dynamically.
- Added Apache deny rules for `.env`, `.sql`, and `.md` files.

## Remaining Risks And Undefined Rules

- The base schema is not fully present in this repository, so migration `003_backend_hardening.sql` must be reviewed against the production database before execution.
- The migration assumes `clientes.email` is intended to be unique/primary because the code relies on `ON DUPLICATE KEY UPDATE`.
- Cancellation policy is not defined. The safest implemented behavior is admin-only delete/reject; no public cancellation endpoint was added.
- Holiday/business closure rules depend on manually maintained `bloqueios`; only common Brazilian fixed/mobile holidays are generated.
- Kiosk count is hard-coded as 1-20 because the project has no kiosk table.
- No audit log table exists for admin actions; this should be added before high-compliance production use.
- No centralized migration runner exists; SQL must be applied manually or through hosting tooling.

## How To Test

- Apply migrations in order, then apply `migrations/003_backend_hardening.sql` after checking duplicate data.
- Login as admin and confirm CSRF token is present on `/parque_ecologico/admin`.
- Submit a kiosk reservation with a valid future weekday date and confirm success.
- Submit an overlapping kiosk reservation for the same kiosk/date/time and confirm HTTP 409 or validation error.
- Submit an overlapping kiosk reservation for a different kiosk/date/time and confirm it is allowed when all other rules pass.
- Submit a technical visit with an inactive guide and confirm it is rejected.
- Submit overlapping technical visits for the same guide/date/time and confirm conflict rejection.
- Approve a pending reservation or visit that conflicts with another non-rejected record and confirm approval is blocked.
- Delete a nonexistent reservation, visit, contact message, guide, or bloqueio and confirm 404.
- Try more than the configured public endpoint limits from the same IP and confirm HTTP 429.
- Try cross-origin credentialed requests from an untrusted origin and confirm CORS is not granted.

## Run Commands

- Local PHP server, if PHP is installed:
  `cd parque_ecologico && php -S localhost:8000`
- Open:
  `http://localhost:8000/parque_ecologico/`
- Seed blocked dates:
  `cd parque_ecologico && php populate_bloqueios.php`

## Production Readiness Evaluation

The backend is materially stronger after this pass: critical admin route bugs, race-prone scheduling, raw error leakage, public endpoint abuse risk, and missing visit status handling were addressed. It is not yet fully government-grade production ready until the production schema is verified, migrations are applied, backups are taken, an audit-log trail is added, and PHP/runtime tests are executed in an environment with PHP and MySQL available.
