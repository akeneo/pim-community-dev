<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Domain\Model\Attribute;

use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeMaxLength
{
    private const LIMIT = 65535;

    /*** @var int */
    private $maxLength;

    public function __construct(int $maxLength)
    {
        Assert::natural($maxLength, sprintf('The maximum length should be positive, %d given', $maxLength));
        Assert::lessThanEq(
            $maxLength,
            self::LIMIT,
            sprintf('The maximum length authorized is %d, %d given', self::LIMIT, $maxLength)
        );
        $this->maxLength = $maxLength;
    }

    public static function fromInteger(int $maxLength) : self
    {
        return new self($maxLength);
    }

    public function intValue(): int
    {
        return $this->maxLength;
    }
}
