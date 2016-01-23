<?php

namespace Akeneo\Component\Memory;

/**
 * Provides human readable format for a given bytes size
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class HumanReadableBytesFormatter
{
    /**
     * @param int $bytes
     * @param int $decimals
     *
     * @return string
     */
    public function format($bytes, $decimals = 2)
    {
        if ($bytes === -1) {
            return 'Unlimited';
        }

        $size = ['B','kB','MB','GB','TB','PB','EB','ZB','YB'];
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . $size[$factor];
    }
}
