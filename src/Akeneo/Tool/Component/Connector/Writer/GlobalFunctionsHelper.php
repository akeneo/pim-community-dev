<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Connector\Writer;

use Box\Spout\Common\Helper\GlobalFunctionsHelper as SpoutGlobalFunctionsHelper;

/**
 * @author    Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GlobalFunctionsHelper extends SpoutGlobalFunctionsHelper
{
    public function fgetcsv($handle, $length = null, $delimiter = null, $enclosure = null)
    {
        // PHP uses '\' as the default escape character. This is not RFC-4180 compliant...
        // To fix that, simply disable the escape character.
        // @see https://bugs.php.net/bug.php?id=43225
        // @see http://tools.ietf.org/html/rfc4180
        $escapeCharacter = PHP_VERSION_ID >= 70400 ? '' : "\0";

        return \fgetcsv($handle, $length, $delimiter, $enclosure, $escapeCharacter);
    }

    public function fputcsv($handle, array $fields, $delimiter = null, $enclosure = null)
    {
        // PHP uses '\' as the default escape character. This is not RFC-4180 compliant...
        // To fix that, simply disable the escape character.
        // @see https://bugs.php.net/bug.php?id=43225
        // @see http://tools.ietf.org/html/rfc4180
        $escapeCharacter = PHP_VERSION_ID >= 70400 ? '' : "\0";

        return \fputcsv($handle, $fields, $delimiter, $enclosure, $escapeCharacter);
    }
}
