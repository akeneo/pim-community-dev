<?php


namespace AkeneoTest\Pim\Enrichment\Integration\Normalizer;

use Akeneo\Test\IntegrationTestsBundle\Sanitizer\DateSanitizer;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NormalizedCategoryCleaner
{
    public static function clean(array &$categoryNormalized)
    {
        self::sanitizeDateFields($categoryNormalized);
    }

    private static function sanitizeDateFields(array &$data)
    {
        if (isset($data['updated'])) {
            $data['updated'] = DateSanitizer::sanitize($data['updated']);
        }
    }
}
