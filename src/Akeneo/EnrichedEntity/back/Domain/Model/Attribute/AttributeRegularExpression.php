<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Domain\Model\Attribute;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeRegularExpression
{
    public const NONE = null;

    /** @var ?string */
    private $regularExpression;

    private function __construct(?string $regularExpression)
    {
        $this->regularExpression = $regularExpression;
    }

    public static function fromString(string $regularExpression): self
    {
        return new self($regularExpression);
    }

    public static function none(): self
    {
        return new self(self::NONE);
    }

    public function isNone(): bool
    {
        return self::NONE === $this->regularExpression;
    }

    public function normalize(): ?string
    {
        return $this->regularExpression;
    }
}
