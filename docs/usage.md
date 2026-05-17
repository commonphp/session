# Usage

`SessionManager` is the main API. It implements `SessionInterface` and delegates persistence to a `SessionDriverInterface`.

Related pages:

- [Lifecycle](lifecycle.md)
- [Session bags and flash data](bags-and-flash.md)
- [Drivers](drivers.md)

## Construct A Manager

```php
use CommonPHP\Session\SessionManager;

$session = new SessionManager();
```

To configure the native driver in one step:

```php
$session = SessionManager::native(
    options: ['cookie_lifetime' => 3600],
    name: 'APPSESSID',
);
```

To use a custom driver instance:

```php
$session = new SessionManager($driver);
```

## Lifecycle Calls

```php
$session->start();

$session->regenerateId();

$session->save();
```

`regenerateId(false)` keeps the old backend record when the driver supports that behavior.

## Root Values

```php
$session->set('user_id', 42);

$userId = $session->get('user_id');

$removed = $session->remove('user_id');
```

Root data methods:

- `get(string $key, mixed $default = null): mixed`
- `set(string $key, mixed $value): static`
- `has(string $key): bool`
- `remove(string $key, mixed $default = null): mixed`
- `pull(string $key, mixed $default = null): mixed`
- `replace(array $values): static`
- `all(): array`
- `clear(): static`

`has()` uses `array_key_exists()`, so keys with `null` values still exist.

## Root Bag

The root bag is useful when an API expects a `SessionBagInterface`.

```php
$bag = $session->bag();

$bag->set('user_id', 42);
```

The root bag points at the whole session payload.

## Named Bags

Named bags point at a single namespace:

```php
$profile = $session->bag('profile');

$profile->set('display_name', 'Ada');
$profile->set('timezone', 'Europe/London');
```

If a namespace already exists and is not an array, `CorruptSessionDataException` is thrown. This protects callers from silently overwriting unrelated scalar data.

## Flash Data

```php
$flash = $session->flash();

$flash->add('success', 'Saved.');
$flash->add('success', 'Queued for review.');

$messages = $flash->get('success');
```

Flash data is stored in the `_flash` namespace by default. Pass a custom namespace when a package needs its own flash channel:

```php
$session->flash('_admin_flash')->add('warning', 'Impersonation enabled.');
```

## Driver Configuration

Drivers can be supplied as instances:

```php
$session->useDriver($driver);
```

Or by class name before the session starts:

```php
$session->setDriver(AppSessionDriver::class, [
    'connectionName' => 'primary',
]);
```

Constructor arguments are passed with PHP named argument unpacking, so the array keys should match the driver constructor parameter names.
