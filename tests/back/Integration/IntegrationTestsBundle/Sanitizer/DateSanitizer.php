<?php

namespace Akeneo\Test\IntegrationTestsBundle\Sanitizer;

/**
 * Sanitize a date.
 *
 * In integration tests, when creating a product for instance, we cannot guess
 * created/updated dates. We can just check if the data match with the constant
 * DATE_FIELD_PATTERN.
 * If the pattern is checked, we return the constant DATE_FIELD_COMPARISON to be
 * able to have an identical comparison element.
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateSanitizer
{
    const DATE_FIELD_COMPARISON = 'this is a date formatted to ISO-8601';
    const DATE_FIELD_PATTERN = '#[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}:[0-9]{2}(?:\+|\-)[0-9]{2}:[0-9]{2}$#';

    /**
     * Replaces date by self::DATE_FIELD_COMPARISON.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    public static function sanitize($data)
    {
        if (1 === preg_match(self::DATE_FIELD_PATTERN, $data)) {
            return self::DATE_FIELD_COMPARISON;
        }

        return $data;
    }
}
