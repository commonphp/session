# Flash Messages

Flash messages are useful when one action needs to communicate with the next request.

```php
<?php

declare(strict_types=1);

use CommonPHP\Session\SessionManager;

$session = new SessionManager();
$session->start();

$session->flash()->add('success', 'Profile updated.');

// Redirect or finish the current request.
$session->save();
```

On the next request:

```php
$session->start();

$messages = $session->flash()->get('success');

foreach ($messages as $message) {
    echo $message . PHP_EOL;
}

$session->save();
```

Use `peek()` if messages should remain available for another consumer:

```php
$messages = $session->flash()->peek('success');
```
