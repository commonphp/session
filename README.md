# CommonPHP Session

CommonPHP Session provides driver-based session management for CommonPHP applications. It defines session contracts, session manager behavior, flash data support, and integration points for native, database-backed, or other session drivers.

The package keeps session behavior explicit while allowing storage details to remain behind focused drivers.

## Requirements

- PHP `^8.5`
- `comphp/runtime:^0.3`

## Installation

Once this package is available through your Composer repositories, install it with:

```bash
composer require comphp/session
```

## Usage

```php
<?php

// TODO: Write usage
```

## Package Notes

This package should provide session management, session drivers, flash data, and session lifecycle behavior. Database-backed or custom storage should live in driver packages.

## Error Handling

Session start failures, invalid drivers, storage failures, and corrupted session data should throw CommonPHP session exceptions.

## Documentation

- [Usage](docs/usage.md)
- [Testing](TESTING.md)
- [Contributing](CONTRIBUTING.md)
- [Security](SECURITY.md)

## License

MIT. See [LICENSE.md](LICENSE.md).
