<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Domain\Model\Attribute;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeIsTextarea
{
    private bool $isTextarea;

    private function __construct(bool $isTextarea)
    {
        $this->isTextarea = $isTextarea;
    }

    public static function fromBoolean(bool $isTextarea): self
    {
        return new self($isTextarea);
    }

    public function isYes(): bool
    {
        return $this->isTextarea;
    }

    public function normalize(): bool
    {
        return $this->isTextarea;
    }

    public function equals(AttributeIsTextarea $otherIsTextarea): bool
    {
        return $this->isTextarea === $otherIsTextarea->isTextarea;
    }
}
