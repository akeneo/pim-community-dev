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

final class MinValidation implements TableValidation
{
    public const KEY = 'min';

    /** @var int|float */
    private $minValue;

    /**
     * @param int|float $minValue
     */
    private function __construct($minValue)
    {
        $this->minValue = $minValue;
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
        return $this->minValue;
    }
}
