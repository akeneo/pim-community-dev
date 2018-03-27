<?php

namespace Akeneo\Test\IntegrationTestsBundle\Sanitizer;

/**
 * Sanitize a media.
 *
 * In integration tests, when uploading a media, we cannot guess the final path.
 * We can just check if the data match with the constant MEDIA_ATTRIBUTE_DATA_PATTERN.
 * If the pattern is checked, we return the constant MEDIA_ATTRIBUTE_DATA_COMPARISON
 * to be able to have an identical comparison element
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaSanitizer
{
    const MEDIA_ATTRIBUTE_DATA_COMPARISON = 'this is a media identifier';
    const MEDIA_ATTRIBUTE_DATA_PATTERN = '#([0-9a-z]/){4}[0-9a-z]{40}_\w+\.[a-zA-Z]+(/download)?$#';

    /**
     * Replaces media attributes data in the $data array by self::MEDIA_ATTRIBUTE_DATA_COMPARISON.
     *
     * @param mixed $data
     *
     * @return mixed
     */
    public static function sanitize($data)
    {
        if (1 === preg_match(self::MEDIA_ATTRIBUTE_DATA_PATTERN, $data)) {
            return self::MEDIA_ATTRIBUTE_DATA_COMPARISON;
        }

        return $data;
    }
}
