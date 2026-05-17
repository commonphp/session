# Error Handling

All package-specific failures extend `SessionException`.

Related pages:

- [Lifecycle](lifecycle.md)
- [Drivers](drivers.md)

## Exception Types

- `SessionException`: base exception for the package.
- `SessionDriverException`: wraps unexpected driver failures.
- `InvalidSessionDriverException`: invalid driver classes, creation failures, or driver switching after start.
- `SessionNotStartedException`: data or lifecycle operation requires an active session.
- `SessionStartException`: driver could not start a session.
- `SessionStorageException`: storage operation failed after start.
- `CorruptSessionDataException`: a named session namespace is not array data.

## Expected Usage Errors

Reading before start throws:

```php
$session = new SessionManager($driver);

$session->get('user_id'); // SessionNotStartedException
```

Switching drivers after start throws:

```php
$session->start();
$session->useDriver($otherDriver); // InvalidSessionDriverException
```

Using a named bag over scalar data throws:

```php
$session->set('profile', 'not an array');
$session->bag('profile'); // CorruptSessionDataException
```

## Driver Failures

Drivers should throw session exceptions directly when they can explain the failure.

Unexpected throwables are wrapped by `SessionManager`:

```php
try {
    $session->start();
} catch (SessionDriverException $exception) {
    $previous = $exception->getPrevious();
}
```

## Native Driver Failures

The native driver captures warnings from `session_start()`, `session_write_close()`, `session_regenerate_id()`, `session_destroy()`, `session_name()`, and `session_id()`.

Captured warnings become package exceptions so callers do not need to install their own temporary PHP error handlers.
