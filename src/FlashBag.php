<?php

declare(strict_types=1);

namespace CommonPHP\Session;

use ArrayIterator;
use CommonPHP\Session\Contracts\FlashBagInterface;
use CommonPHP\Session\Exceptions\CorruptSessionDataException;
use Traversable;

/**
 * @implements FlashBagInterface
 */
final class FlashBag implements FlashBagInterface
{
    /**
     * @var mixed
     */
    private mixed $messages;

    /**
     * @param array<string, mixed> $messages
     */
    public function __construct(array &$messages, private readonly ?string $name = null)
    {
        $this->messages = &$messages;
        $this->normalize();
    }

    /**
     * @param array<string, mixed> $messages
     */
    public static function create(array $messages = []): self
    {
        return new self($messages);
    }

    public function add(string $type, mixed $message): static
    {
        $this->normalize();
        $messages = &$this->messages();
        $messages[$type] ??= [];
        $messages[$type][] = $message;

        return $this;
    }

    public function set(string $type, array $messages): static
    {
        $current = &$this->messages();
        $current[$type] = array_values($messages);

        return $this;
    }

    public function get(string $type, array $default = []): array
    {
        if (!$this->has($type)) {
            return array_values($default);
        }

        $current = &$this->messages();
        $messages = $current[$type];
        unset($current[$type]);

        return $messages;
    }

    public function peek(string $type, array $default = []): array
    {
        $messages = &$this->messages();

        return $this->has($type) ? $messages[$type] : array_values($default);
    }

    public function has(string $type): bool
    {
        $this->normalize();
        $messages = &$this->messages();

        return isset($messages[$type]) && $messages[$type] !== [];
    }

    public function all(): array
    {
        $messages = $this->peekAll();
        $this->messages = [];

        return $messages;
    }

    public function peekAll(): array
    {
        $this->normalize();

        return $this->messages;
    }

    public function clear(?string $type = null): static
    {
        if ($type === null) {
            $messages = &$this->messages();
            $messages = [];

            return $this;
        }

        $messages = &$this->messages();
        unset($messages[$type]);

        return $this;
    }

    public function count(): int
    {
        $this->normalize();

        return count($this->messages());
    }

    /**
     * @return Traversable<string, list<mixed>>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->peekAll());
    }

    private function normalize(): void
    {
        $current = &$this->messages();

        foreach ($current as $type => $messages) {
            $key = (string) $type;

            if ($key !== $type) {
                unset($current[$type]);
            }

            if (!is_array($messages)) {
                $current[$key] = [$messages];

                continue;
            }

            $current[$key] = array_values($messages);
        }
    }

    /**
     * @return array<string, list<mixed>>
     */
    private function &messages(): array
    {
        if (!is_array($this->messages)) {
            throw CorruptSessionDataException::forBag($this->name);
        }

        return $this->messages;
    }
}
