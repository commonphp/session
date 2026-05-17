# Package Boundaries

`comphp/session` owns session state and session persistence abstractions.

## This Package Owns

- Session lifecycle: start, save, invalidate, regenerate ID.
- Session data access.
- Root and named session bags.
- Flash messages.
- Session driver contracts.
- Native PHP session integration.
- Session-specific exceptions.

## This Package Does Not Own

- HTTP request or response objects.
- Cookie response emission outside native PHP session behavior.
- Authentication.
- Authorization.
- CSRF token generation or validation.
- Database connection management.
- Routing, controllers, templates, or UI.

## Related Packages

- `comphp/http` owns request and response primitives.
- `comphp/security` owns CSRF, authorization, password hashing, and security context behavior.
- `comphp/auth` owns authentication state and identity.
- `comphp/database` owns database connections and queries.

## Driver Packages

Database-backed session storage should live in driver packages. A driver package should implement `SessionDriverInterface` and depend on the storage package it needs.

Examples:

- `comphp/session-comphp-database`
- `comphp/session-mysqldb`

Keeping storage backends outside this package keeps the core API easy to understand, debug, use, and update.
