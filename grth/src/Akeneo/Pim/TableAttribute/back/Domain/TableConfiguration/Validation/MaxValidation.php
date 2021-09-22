<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Validation;

use Webmozart\Assert\Assert;

final class MaxValidation implements TableValidation
{
    public const KEY = 'max';

    /** @var int|float */
    private $maxValue;

    /**
     * @param int|float $maxValue
     */
    private function __construct($maxValue)
    {
        $this->maxValue = $maxValue;
    }

    /**
     * {@inheritDoc}
     */
    public static function fromValue($value): TableValidation
    {
        Assert::true(is_int($value) || is_float($value), 'Value should be an integer or a float');

        return new self($value);
    }

    /**
     * {@inheritDoc}
     */
    public function getValue()
    {
        return $this->maxValue;
    }
}
