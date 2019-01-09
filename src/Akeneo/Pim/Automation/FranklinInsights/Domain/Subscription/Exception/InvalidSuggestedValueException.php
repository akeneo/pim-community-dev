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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class InvalidSuggestedValueException extends \InvalidArgumentException
{
    /**
     * @return InvalidSuggestedValueException
     */
    public static function invalidValue(): InvalidSuggestedValueException
    {
        return new static('"value" must be a string or an array of strings');
    }

    /**
     * @return InvalidSuggestedValueException
     */
    public static function emptyAttributeCode(): InvalidSuggestedValueException
    {
        return new static('"pimAttributeCode" must not be empty');
    }

    /**
     * @return InvalidSuggestedValueException
     */
    public static function emptyValue(): InvalidSuggestedValueException
    {
        return new static('"value" must not be empty');
    }
}
