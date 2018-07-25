<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Domain\Model\Attribute;

use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeMaxFileSize
{
    private const LIMIT = 9999.99;

    /*** @var int */
    private $maxLength;

    public function __construct(float $maxFileSize)
    {
        Assert::greaterThanEq($maxFileSize, 0, sprintf('The maximum file size should be positive, %d given', $maxFileSize));
        Assert::lessThanEq(
            $maxFileSize,
            self::LIMIT,
            sprintf('The maximum file size (in MB) authorized is %.2F, %.2F given', self::LIMIT, $maxFileSize)
        );
        $this->maxLength = $maxFileSize;
    }

    public static function fromFloat(float $maxLength) : self
    {
        return new self($maxLength);
    }
}
