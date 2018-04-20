<?php

declare(strict_types=1);

namespace Akeneo\Test\Common\EntityWithValue;

final class Code
{
    /** @var string */
    private $code;

    /**
     * @param string $code
     *
     * @throws \InvalidArgumentException
     */
    private function __construct(string $code)
    {
        $this->code = $code;
    }

    /**
     * @return Code
     */
    public static function emptyCode(): Code
    {
        return new self('');
    }

    /**
     * @param string $code
     *
     * @return Code
     *
     * @throws \InvalidArgumentException
     */
    public static function fromString(string $code): Code
    {
        return new self($code);
    }

    /**
     * @param object $entity
     *
     * @return Code
     */
    public static function fromEntity($entity): Code
    {
        if (!\method_exists($entity, 'getCode')) {
            throw new \InvalidArgumentException(
                sprintf('The given object %s does not have the method "getCode()"', get_class($entity))
            );
        }

        return new self($entity->getCode());
    }

    /**
     * @return bool
     */
    public function empty(): bool
    {
        return '' === $this->code;
    }

    /**
     * @return string
     */
    public function toStandardFormat(): string
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->code;
    }
}
