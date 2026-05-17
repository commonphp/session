<?php

declare(strict_types=1);

namespace CommonPHP\Session;

use CommonPHP\Session\Contracts\FlashBagInterface;
use CommonPHP\Session\Contracts\SessionBagInterface;
use CommonPHP\Session\Contracts\SessionDriverInterface;
use CommonPHP\Session\Contracts\SessionInterface;
use CommonPHP\Session\Drivers\NativeSessionDriver;
use CommonPHP\Session\Enums\SessionStatus;
use CommonPHP\Session\Exceptions\CorruptSessionDataException;
use CommonPHP\Session\Exceptions\InvalidSessionDriverException;
use CommonPHP\Session\Exceptions\SessionDriverException;
use CommonPHP\Session\Exceptions\SessionException;
use Throwable;

final class SessionManager implements SessionInterface
{
    public const DEFAULT_FLASH_NAMESPACE = '_flash';

    private SessionDriverInterface $driver;

    private ?SessionBagInterface $rootBag = null;

    /**
     * @var array<string, SessionBagInterface>
     */
    private array $bags = [];

    /**
     * @var array<string, FlashBagInterface>
     */
    private array $flashBags = [];

    public function __construct(?SessionDriverInterface $driver = null)
    {
        $this->driver = $driver ?? new NativeSessionDriver();
    }

    /**
     * @param array<string, mixed> $options
     */
    public static function native(array $options = [], ?string $name = null, ?string $id = null): self
    {
        return new self(new NativeSessionDriver($options, $name, $id));
    }

    /**
     * @param class-string<SessionDriverInterface>|SessionDriverInterface $driver
     * @param array<string, mixed> $config
     */
    public function setDriver(string|SessionDriverInterface $driver, array $config = []): static
    {
        if ($this->isStarted()) {
            throw InvalidSessionDriverException::cannotSwitchStartedSession();
        }

        if ($driver instanceof SessionDriverInterface) {
            if ($config !== []) {
                throw InvalidSessionDriverException::forCreation(
                    $driver->getName(),
                    new SessionDriverException('Driver instances do not accept constructor configuration.'),
                );
            }

            return $this->useDriver($driver);
        }

        if (!is_a($driver, SessionDriverInterface::class, true)) {
            throw InvalidSessionDriverException::forClass($driver);
        }

        try {
            return $this->useDriver(new $driver(...$config));
        } catch (Throwable $throwable) {
            throw InvalidSessionDriverException::forCreation($driver, $throwable);
        }
    }

    public function useDriver(SessionDriverInterface $driver): static
    {
        if ($this->isStarted()) {
            throw InvalidSessionDriverException::cannotSwitchStartedSession();
        }

        $this->driver = $driver;
        $this->resetBags();

        return $this;
    }

    public function getDriver(): SessionDriverInterface
    {
        return $this->driver;
    }

    public function start(): static
    {
        $this->operate('start', fn (SessionDriverInterface $driver): mixed => $driver->start());

        return $this;
    }

    public function save(): static
    {
        $this->operate('save', fn (SessionDriverInterface $driver): mixed => $driver->save());
        $this->resetBags();

        return $this;
    }

    public function invalidate(): static
    {
        $this->operate('invalidate', fn (SessionDriverInterface $driver): mixed => $driver->invalidate());
        $this->resetBags();

        return $this;
    }

    public function regenerateId(bool $deleteOldSession = true): string
    {
        return $this->operate(
            'regenerate id',
            fn (SessionDriverInterface $driver): string => $driver->regenerateId($deleteOldSession),
        );
    }

    public function status(): SessionStatus
    {
        return $this->driver->status();
    }

    public function isStarted(): bool
    {
        return $this->status()->isActive();
    }

    public function id(): string
    {
        return $this->driver->id();
    }

    public function setId(string $id): static
    {
        $this->operate('set id', fn (SessionDriverInterface $driver): mixed => $driver->setId($id));

        return $this;
    }

    public function name(): string
    {
        return $this->driver->name();
    }

    public function setName(string $name): static
    {
        $this->operate('set name', fn (SessionDriverInterface $driver): mixed => $driver->setName($name));

        return $this;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $data = &$this->data();

        return array_key_exists($key, $data) ? $data[$key] : $default;
    }

    public function set(string $key, mixed $value): static
    {
        $data = &$this->data();
        $data[$key] = $value;
        $this->dropCachedNamespace($key);

        return $this;
    }

    public function has(string $key): bool
    {
        $data = &$this->data();

        return array_key_exists($key, $data);
    }

    public function remove(string $key, mixed $default = null): mixed
    {
        $data = &$this->data();

        if (!array_key_exists($key, $data)) {
            return $default;
        }

        $value = $data[$key];
        unset($data[$key]);
        $this->dropCachedNamespace($key);

        return $value;
    }

    public function pull(string $key, mixed $default = null): mixed
    {
        return $this->remove($key, $default);
    }

    public function replace(array $values): static
    {
        $data = &$this->data();
        $data = $values;
        $this->resetBags();

        return $this;
    }

    public function all(): array
    {
        return $this->data();
    }

    public function clear(): static
    {
        $data = &$this->data();
        $data = [];
        $this->resetBags();

        return $this;
    }

    public function bag(?string $name = null): SessionBagInterface
    {
        if ($name === null) {
            if ($this->rootBag === null) {
                $data = &$this->data();
                $this->rootBag = new SessionBag($data, 'root');
            }

            return $this->rootBag;
        }

        $data = &$this->namespace($name);
        $this->bags[$name] ??= new SessionBag($data, $name);

        return $this->bags[$name];
    }

    public function flash(string $namespace = self::DEFAULT_FLASH_NAMESPACE): FlashBagInterface
    {
        $data = &$this->namespace($namespace);
        $this->flashBags[$namespace] ??= new FlashBag($data, $namespace);

        return $this->flashBags[$namespace];
    }

    /**
     * @return array<string, mixed>
     */
    private function &data(): array
    {
        try {
            $data = &$this->driver->data();

            return $data;
        } catch (SessionException $exception) {
            throw $exception;
        } catch (Throwable $throwable) {
            throw SessionDriverException::forOperation('access data', $throwable);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function &namespace(string $name): array
    {
        $data = &$this->data();

        if (!array_key_exists($name, $data)) {
            $data[$name] = [];
        }

        if (!is_array($data[$name])) {
            throw CorruptSessionDataException::forNamespace($name);
        }

        $namespace = &$data[$name];

        return $namespace;
    }

    private function resetBags(): void
    {
        $this->rootBag = null;
        $this->bags = [];
        $this->flashBags = [];
    }

    private function dropCachedNamespace(string $name): void
    {
        if ($this->rootBag !== null) {
            $this->rootBag = null;
        }

        unset($this->bags[$name], $this->flashBags[$name]);
    }

    private function operate(string $operation, callable $callback): mixed
    {
        try {
            return $callback($this->driver);
        } catch (SessionException $exception) {
            throw $exception;
        } catch (Throwable $throwable) {
            throw SessionDriverException::forOperation($operation, $throwable);
        }
    }
}
