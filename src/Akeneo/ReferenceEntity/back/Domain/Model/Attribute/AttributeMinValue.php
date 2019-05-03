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
class AttributeMinValue
{
    private const NO_MINIMUM = null;

    /** @var ?string */
    private $minValue;

    private function __construct(?string $minValue)
    {
        Assert::nullOrStringNotEmpty($minValue, 'The min value cannot be empty');
        if (self::NO_MINIMUM !== $minValue) {
            Assert::numeric($minValue);
            Assert::greaterThanEq((float) $maxFileSize, 0, sprintf('The maximum file size should be positive, %d given', $maxFileSize));
            Assert::lessThanEq(
                (float) $maxFileSize,
                self::LIMIT,
                sprintf('The maximum file size (in MB) authorized is %.2F, %.2F given', self::LIMIT, $maxFileSize)
            );
        }

        $this->minValue = $minValue;
    }

    public static function fromString(string $minValue): self
    {
        return new self($minValue);
    }

    public function normalize(): string
    {
        return $this->minValue;
    }
}
