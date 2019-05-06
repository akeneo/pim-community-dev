<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Domain\Model\Attribute;

use Webmozart\Assert\Assert;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class AttributeMaxValue
{
    private const NO_MAXIMUM = null;

    /** @var ?string */
    private $maxValue;

    private function __construct(?string $maxValue)
    {
        Assert::nullOrStringNotEmpty($maxValue, 'The max value cannot be empty');
        if (self::NO_MAXIMUM !== $maxValue) {
            Assert::numeric($maxValue);
        }

        $this->maxValue = $maxValue;
    }

    public static function fromString(string $maxValue): self
    {
        return new self($maxValue);
    }

    public static function noMaximum(): self
    {
        return new self(self::NO_MAXIMUM);
    }

    public function normalize(): ?string
    {
        return $this->maxValue;
    }
}
