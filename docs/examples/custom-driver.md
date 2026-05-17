# Custom Driver

A custom driver implements `SessionDriverInterface`. Extending `AbstractSessionDriver` gives the driver a default name and guard helpers.

```php
<?php

declare(strict_types=1);

use CommonPHP\Session\Contracts\AbstractSessionDriver;
use CommonPHP\Session\Enums\SessionStatus;

final class ArraySessionDriver extends AbstractSessionDriver
{
    private array $data = [];

    private bool $active = false;

    public function start(): void
    {
        $this->active = true;
    }

    public function save(): void
    {
        $this->assertCanAccessData('save session data');
        $this->active = false;
    }

    public function invalidate(): void
    {
        $this->assertCanAccessData('invalidate the session');
        $this->data = [];
        $this->active = false;
    }

    public function regenerateId(bool $deleteOldSession = true): string
    {
        $this->assertCanAccessData('regenerate the session id');

        return bin2hex(random_bytes(16));
    }

    public function status(): SessionStatus
    {
        return $this->active ? SessionStatus::Active : SessionStatus::None;
    }

    public function id(): string
    {
        return 'array-session';
    }

    public function setId(string $id): void
    {
    }

    public function name(): string
    {
        return 'ARRAYSESSID';
    }

    public function setName(string $name): void
    {
    }

    public function &data(): array
    {
        $this->assertCanAccessData('access session data');

        return $this->data;
    }
}
```

Use it with the manager:

```php
use CommonPHP\Session\SessionManager;

$session = new SessionManager(new ArraySessionDriver());

$session->start();
$session->set('user_id', 42);
```
