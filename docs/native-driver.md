# Native Driver

`NativeSessionDriver` wraps PHP's built-in session engine. It is the default driver used by `SessionManager`.

## Create Directly

```php
use CommonPHP\Session\Drivers\NativeSessionDriver;

$driver = new NativeSessionDriver(
    options: ['cookie_lifetime' => 3600],
    configuredName: 'APPSESSID',
);
```

## Create Through The Manager

```php
use CommonPHP\Session\SessionManager;

$session = SessionManager::native(
    options: ['cookie_lifetime' => 3600],
    name: 'APPSESSID',
);
```

## Options

The options array is passed to `session_start()`:

```php
$session = SessionManager::native([
    'cookie_lifetime' => 3600,
    'cookie_secure' => true,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
]);
```

Use regular PHP session configuration for settings that should apply process-wide.

## Name And ID

Set the session name or ID before start:

```php
$session->setName('APPSESSID');
$session->setId('known-id');
$session->start();
```

The native driver rejects empty names and IDs. It also rejects name or ID changes while a session is active.

## Status

The driver maps `session_status()` to `SessionStatus`:

- `PHP_SESSION_DISABLED` becomes `SessionStatus::Disabled`;
- `PHP_SESSION_NONE` becomes `SessionStatus::None`;
- `PHP_SESSION_ACTIVE` becomes `SessionStatus::Active`.

## Data

`data()` returns `$_SESSION` by reference.

```php
$driver->start();

$data = &$driver->data();
$data['user_id'] = 42;
```

If `$_SESSION` is missing or not an array after start, the native driver initializes it to an empty array.

## Save

```php
$driver->save();
```

This calls `session_write_close()`.

## Invalidate

```php
$driver->invalidate();
```

This clears `$_SESSION`, expires the active session cookie when cookie sessions are enabled and headers are still available, then calls `session_destroy()`.

## Warning Handling

Native `session_*` functions often report failures as warnings. The driver captures those warnings and throws CommonPHP exceptions such as `SessionStartException` or `SessionStorageException`.
