<?php

namespace Pim\Component\Catalog\tests\integration\Normalizer;

use Akeneo\Test\Integration\DateSanitizer;

/**
 * Cleans a normalized product (aka, an array of data) so that it can be compared with the expected result
 * of the normalization. This cleaner:
 *      - sorts recursively the values by keys,
 *      - takes care of the inconsistent "created_at" and "updated_at" fields.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NormalizedProductCleaner
{
    /**
     * @param array $productNormalized
     */
    public static function clean(array &$productNormalized)
    {
        self::sanitizeDateFields($productNormalized);
        self::sortValues($productNormalized['values']);
    }

    /**
     * Sort values by attribute code, then by channel, then by locale.
     *
     * @param array $values
     */
    public static function sortValues(array &$values)
    {
        self::ksortRecursive($values);
    }

    /**
     * @param mixed $array
     * @param int   $sortFlags
     *
     * @return bool
     */
    private static function ksortRecursive(&$array, $sortFlags = SORT_REGULAR)
    {
        if (!is_array($array)) {
            return false;
        }

        ksort($array, $sortFlags);
        foreach ($array as &$arr) {
            self::ksortRecursive($arr, $sortFlags);
        }

        return true;
    }

    /**
     * Replaces dates fields (created/updated) in the $data array by self::DATE_FIELD_COMPARISON.
     *
     * @param array $data
     */
    private static function sanitizeDateFields(array &$data)
    {
        if (isset($data['created'])) {
            $data['created'] = DateSanitizer::sanitize($data['created']);
        }

        if (isset($data['updated'])) {
            $data['updated'] = DateSanitizer::sanitize($data['updated']);
        }
    }
}
