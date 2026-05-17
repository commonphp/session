# Testing And QA

The package has a local PHPUnit configuration:

```bash
vendor/bin/phpunit -c package/session/phpunit.xml.dist
```

On Windows:

```bash
vendor\bin\phpunit.bat -c package\session\phpunit.xml.dist
```

## Test Layout

- `tests/bootstrap.php` loads either the package vendor autoloader or the workspace root autoloader.
- `tests/Fixtures` contains small test drivers.
- `tests/Unit` contains object-level tests for contracts, bags, manager behavior, native driver behavior, and exceptions.

## What The Suite Covers

The suite covers:

- `SessionManager` lifecycle delegation;
- driver switching and invalid driver errors;
- root values and named bags;
- flash message read-once behavior;
- corrupt namespace detection;
- native session start, data, regenerate, save, invalidate, and warning conversion;
- contract implementation;
- session status mapping;
- exception factory messages and previous exceptions.

## Native Session Tests

Native session tests run in separate PHP processes because PHP session state is global. They use a temporary session save path under `.phpunit.cache/sessions`.

When adding native tests:

- avoid writing output before `session_start()`;
- keep cookie usage disabled unless a test specifically needs it;
- cleanly close, abort, or destroy active sessions;
- prefer separate processes for tests that mutate session globals.

## Driver Tests

Custom drivers should have their own package tests. At minimum, driver tests should prove:

- `start()` activates the session;
- `data()` returns the active payload by reference;
- `save()` persists or closes as expected;
- `invalidate()` clears storage;
- `regenerateId()` returns the new ID;
- storage failures throw session exceptions.
