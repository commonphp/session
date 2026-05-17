# Lifecycle

Session lifecycle is explicit. A driver can report three states through `SessionStatus`:

- `SessionStatus::Disabled`
- `SessionStatus::None`
- `SessionStatus::Active`

`SessionManager::isStarted()` returns true only for `SessionStatus::Active`.

## Start

```php
$session->start();
```

Starting delegates to the active driver. The native driver calls `session_start()` and initializes `$_SESSION` as an array when needed.

Calling `start()` on an already active native session is safe and returns without starting twice.

## Access Data

Data access requires an active session:

```php
$session->start();

$session->set('user_id', 42);
```

If data is read or written before start, drivers should throw `SessionNotStartedException`.

## Regenerate The ID

```php
$newId = $session->regenerateId();
```

Use this after privilege changes, sign-in, or other flows where session fixation matters.

The boolean argument is passed to the driver:

```php
$newId = $session->regenerateId(deleteOldSession: false);
```

## Save

```php
$session->save();
```

The native driver calls `session_write_close()`. After save, the native status returns to `SessionStatus::None`.

Use `save()` when a request has finished mutating session data or when a long-running request should release the session lock.

## Invalidate

```php
$session->invalidate();
```

Invalidation clears the active payload and asks the driver to destroy storage for the current session. Use it for logout, credential reset, and other flows that should discard all session state.

## Configure Before Start

The active driver can be replaced only before the session starts:

```php
$session->useDriver($driver);
```

The native driver also requires ID and name changes before start:

```php
$session->setName('APPSESSID');
$session->setId('known-id');
$session->start();
```

Trying to switch drivers or reconfigure the native session while active throws a session exception.
