<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Domain\Supplier;

final class Code
{
    private const MAX_LENGTH = 200;

    private function __construct(private string $code)
    {
        if ('' === $code) {
            throw new \InvalidArgumentException('The supplier code cannot be empty.');
        }

        if (strlen($code) > self::MAX_LENGTH) {
            throw new \InvalidArgumentException(
                sprintf(
                    'The supplier code is too long. It should have %d characters or less.',
                    self::MAX_LENGTH
                )
            );
        }

        $code = trim($code);

        if (!preg_match('/^[a-z0-9_]+$/', $code)) {
            throw new \InvalidArgumentException(
                'The supplier code can only contain lowercase letters, numbers and underscores.'
            );
        }

        $this->code = $code;
    }

    public static function fromString(string $code): self
    {
        return new self($code);
    }

    public function equals(self $other): bool
    {
        return $this->code === $other->code;
    }

    public function __toString(): string
    {
        return $this->code;
    }
}
