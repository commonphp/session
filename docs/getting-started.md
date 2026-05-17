# Getting Started

CommonPHP Session provides a single session manager with pluggable drivers.

## Install

```bash
composer require comphp/session
```

In this monorepo, the package is also available through the workspace path repository and the root Composer autoloader.

## Start A Session

```php
<?php

declare(strict_types=1);

use CommonPHP\Session\SessionManager;

$session = new SessionManager();

$session->start();
$session->set('user_id', 42);
```

The default manager uses `NativeSessionDriver`, which wraps PHP's built-in `session_*` functions.

## Read Session Data

```php
$userId = $session->get('user_id');

if ($session->has('user_id')) {
    // The key exists, even if its value is null.
}
```

`get()` accepts a default value:

```php
$timezone = $session->get('timezone', 'UTC');
```

## Use A Named Bag

Named bags keep related values together inside one session namespace:

```php
$session->bag('preferences')->set('theme', 'dark');

$theme = $session->bag('preferences')->get('theme', 'light');
```

The stored payload is:

```php
[
    'preferences' => [
        'theme' => 'dark',
    ],
]
```

## Use Flash Messages

Flash messages are read-once values:

```php
$session->flash()->add('success', 'Profile updated.');

$messages = $session->flash()->get('success');
```

After `get()`, that message type is removed. Use `peek()` when the caller should inspect messages without consuming them.

## Save Or Invalidate

```php
$session->save();

// Or, for logout and similar flows:
$session->invalidate();
```

`save()` closes the active session. `invalidate()` clears data and asks the driver to destroy the active session.
