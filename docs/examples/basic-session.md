# Basic Session

```php
<?php

declare(strict_types=1);

use CommonPHP\Session\SessionManager;

$session = new SessionManager();

$session->start();

if (!$session->has('visits')) {
    $session->set('visits', 0);
}

$session->set('visits', $session->get('visits') + 1);

$preferences = $session->bag('preferences');
$preferences->set('theme', $preferences->get('theme', 'light'));

$session->save();
```

The root value `visits` and named bag `preferences` share the same session payload.
