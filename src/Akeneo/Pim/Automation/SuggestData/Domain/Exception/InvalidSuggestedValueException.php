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

namespace Akeneo\Pim\Automation\SuggestData\Domain\Exception;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class InvalidSuggestedValueException extends \InvalidArgumentException
{
    /**
     * @param string $message
     */
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

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
    public static function emptyName(): InvalidSuggestedValueException
    {
        return new static('"name" must not be empty');
    }

    /**
     * @return InvalidSuggestedValueException
     */
    public static function emptyValue(): InvalidSuggestedValueException
    {
        return new static('"value" must not be empty');
    }
}
