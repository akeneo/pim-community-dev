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

namespace Akeneo\AssetManager\Domain\Model\Attribute;

use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeMaxFileSize
{
    private const NO_LIMIT = null;
    private const LIMIT = 9999.99;

    /*** @var ?string */
    private ?string $maxFileSize = null;

    private function __construct(?string $maxFileSize)
    {
        Assert::nullOrStringNotEmpty($maxFileSize, 'The max file size cannot be empty');
        if (self::NO_LIMIT !== $maxFileSize) {
            Assert::greaterThanEq((float) $maxFileSize, 0, sprintf('The maximum file size should be positive, %d given', $maxFileSize));
            Assert::lessThanEq(
                (float) $maxFileSize,
                self::LIMIT,
                sprintf('The maximum file size (in MB) authorized is %.2F, %.2F given', self::LIMIT, $maxFileSize)
            );
        }
        $this->maxFileSize = $maxFileSize;
    }

    public static function fromString(string $maxFileSize) : self
    {
        return new self($maxFileSize);
    }

    public static function noLimit(): self
    {
        return new self(self::NO_LIMIT);
    }

    public function hasLimit(): bool
    {
        return null !== $this->maxFileSize;
    }

    public function floatValue(): float
    {
        return (float) $this->maxFileSize;
    }

    public function normalize(): ?string
    {
        return $this->maxFileSize;
    }
}
