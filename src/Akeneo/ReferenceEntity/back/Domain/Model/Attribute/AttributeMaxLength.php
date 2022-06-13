<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Domain\Model\Attribute;

use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeMaxLength
{
    public const NO_LIMIT = null;
    public const MAX_LIMIT = 65535;

    private function __construct(
        private ?int $maxLength
    ) {
        if (self::NO_LIMIT !== $maxLength) {
            Assert::natural($maxLength, sprintf('The maximum length should be positive, %d given', $maxLength));
            Assert::lessThanEq(
                $maxLength,
                self::MAX_LIMIT,
                sprintf('The maximum length authorized is %d, %d given', self::MAX_LIMIT, $maxLength)
            );
        }
    }

    public static function fromInteger(int $maxLength): self
    {
        return new self($maxLength);
    }

    public static function noLimit(): self
    {
        return new self(self::NO_LIMIT);
    }

    public function intValue(): ?int
    {
        return $this->maxLength;
    }

    public function normalize(): ?int
    {
        return $this->maxLength;
    }
}
