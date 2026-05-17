# Session Bags And Flash Data

Session bags are small views over session arrays. They keep the API simple while making namespaced session data easy to test and reason about.

Related pages:

- [Usage](usage.md)
- [Error handling](error-handling.md)

## SessionBag

`SessionBag` implements `SessionBagInterface`.

```php
$bag = $session->bag('preferences');

$bag->set('theme', 'dark');
$bag->set('density', 'compact');
```

Available methods:

- `get(string $key, mixed $default = null): mixed`
- `set(string $key, mixed $value): static`
- `has(string $key): bool`
- `remove(string $key, mixed $default = null): mixed`
- `pull(string $key, mixed $default = null): mixed`
- `replace(array $values): static`
- `all(): array`
- `clear(): static`
- `isEmpty(): bool`
- `count(): int`
- `getIterator(): Traversable`

`remove()` and `pull()` are aliases. Both return the removed value or the supplied default.

## Root Bag

```php
$root = $session->bag();
```

The root bag points at the complete session payload. It is useful for generic tools, tests, and integrations that should not care whether data is namespaced.

## Named Bags

```php
$cart = $session->bag('cart');
$cart->set('items', [$itemId => 2]);
```

Named bags are backed by arrays. If the namespace exists but contains a scalar value, the manager throws `CorruptSessionDataException`.

## FlashBag

`FlashBag` implements `FlashBagInterface`.

```php
$flash = $session->flash();

$flash->add('success', 'Saved.');
$flash->add('error', 'Payment failed.');
```

Available methods:

- `add(string $type, mixed $message): static`
- `set(string $type, array $messages): static`
- `get(string $type, array $default = []): array`
- `peek(string $type, array $default = []): array`
- `has(string $type): bool`
- `all(): array`
- `peekAll(): array`
- `clear(?string $type = null): static`
- `count(): int`
- `getIterator(): Traversable`

## Read Once Behavior

`get()` consumes messages for one type:

```php
$messages = $session->flash()->get('success');
```

`all()` consumes every message:

```php
$messagesByType = $session->flash()->all();
```

Use `peek()` or `peekAll()` when rendering or diagnostics should inspect messages without consuming them.

## Custom Flash Namespaces

The default namespace is `_flash`.

```php
$session->flash('_admin_flash')->add('warning', 'Admin mode enabled.');
```

Custom namespaces are useful when independent packages share one session but should not consume each other's messages.
