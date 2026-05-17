# Drivers

Session drivers own persistence and lifecycle details. `SessionManager` owns the public session API and delegates storage operations to one active driver.

Related pages:

- [Native driver](native-driver.md)
- [Lifecycle](lifecycle.md)
- [Examples: custom driver](examples/custom-driver.md)

## Contract

```php
namespace CommonPHP\Session\Contracts;

use CommonPHP\Runtime\Contracts\DriverInterface;
use CommonPHP\Session\Enums\SessionStatus;

interface SessionDriverInterface extends DriverInterface
{
    public function start(): void;
    public function save(): void;
    public function invalidate(): void;
    public function regenerateId(bool $deleteOldSession = true): string;
    public function status(): SessionStatus;
    public function id(): string;
    public function setId(string $id): void;
    public function name(): string;
    public function setName(string $name): void;
    public function &data(): array;
}
```

`data()` returns the active payload by reference so bags and the manager can mutate the same backing array.

## AbstractSessionDriver

`AbstractSessionDriver` provides:

- `getName()`, returning the concrete class name;
- `assertCanAccessData()`, which throws when the session is not active;
- `assertSessionSupport()`, which throws when sessions are disabled.

Custom drivers can extend it to keep guard behavior consistent with the native driver.

## Register A Driver Instance

```php
$session = new SessionManager($driver);

// Or before start:
$session->useDriver($driver);
```

Instances are direct and easy to test.

## Register By Class Name

```php
$session->setDriver(DatabaseSessionDriver::class, [
    'connectionName' => 'primary',
    'table' => 'sessions',
]);
```

The manager creates the driver with named argument unpacking. Array keys must match constructor parameter names.

## Driver Boundaries

Drivers should:

- start, save, destroy, and regenerate storage;
- expose active data as an array reference;
- translate storage failures to session exceptions where possible;
- remain independent of HTTP, auth, UI, and routing concerns.

Drivers should not:

- render responses;
- authorize users;
- validate CSRF tokens;
- know about controllers or routes.

Database-backed session storage belongs in driver packages such as `comphp/session-comphp-database`, not in `comphp/session`.
