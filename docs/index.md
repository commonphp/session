# CommonPHP Session Documentation

CommonPHP Session is a small, driver-based session package. It owns session lifecycle behavior, session data access, named data bags, flash messages, and native PHP session integration.

The package is intentionally explicit. A session must be started before data can be read or written, storage details live behind `SessionDriverInterface`, and expected session failures are reported through CommonPHP session exceptions.

## Start Here

- [Getting started](getting-started.md)
- [Usage](usage.md)
- [Package boundaries](package-boundaries.md)

## Session Concepts

- [Lifecycle](lifecycle.md)
- [Session bags and flash data](bags-and-flash.md)
- [Drivers](drivers.md)
- [Native driver](native-driver.md)
- [Error handling](error-handling.md)

## Examples

- [Examples index](examples/index.md)
- [Basic session](examples/basic-session.md)
- [Flash messages](examples/flash-messages.md)
- [Custom driver](examples/custom-driver.md)

## Development

- [Testing and QA](testing.md)

## Public API Map

Entry points:

- `CommonPHP\Session\SessionManager`
- `CommonPHP\Session\SessionBag`
- `CommonPHP\Session\FlashBag`

Contracts:

- `CommonPHP\Session\Contracts\SessionInterface`
- `CommonPHP\Session\Contracts\SessionDriverInterface`
- `CommonPHP\Session\Contracts\SessionBagInterface`
- `CommonPHP\Session\Contracts\FlashBagInterface`
- `CommonPHP\Session\Contracts\AbstractSessionDriver`

Drivers:

- `CommonPHP\Session\Drivers\NativeSessionDriver`

Enums:

- `CommonPHP\Session\Enums\SessionStatus`

Exceptions:

- `CommonPHP\Session\Exceptions\SessionException`
- `CommonPHP\Session\Exceptions\SessionDriverException`
- `CommonPHP\Session\Exceptions\InvalidSessionDriverException`
- `CommonPHP\Session\Exceptions\SessionNotStartedException`
- `CommonPHP\Session\Exceptions\SessionStartException`
- `CommonPHP\Session\Exceptions\SessionStorageException`
- `CommonPHP\Session\Exceptions\CorruptSessionDataException`
