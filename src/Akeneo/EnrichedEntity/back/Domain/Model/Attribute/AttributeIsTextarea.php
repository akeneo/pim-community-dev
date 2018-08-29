<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Domain\Model\Attribute;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeIsTextarea
{
    /** @var bool */
    private $isTextArea;

    private function __construct(bool $isTextArea)
    {
        $this->isTextArea = $isTextArea;
    }

    public static function fromBoolean(bool $isTextArea): self
    {
        return new self($isTextArea);
    }

    public function isYes(): bool
    {
        return $this->isTextArea;
    }

    public function normalize(): bool
    {
        return $this->isTextArea;
    }

    public function equals(AttributeIsTextarea $otherIsTextArea): bool
    {
        return $this->isTextArea === $otherIsTextArea->isTextArea;
    }
}
