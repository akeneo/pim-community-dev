<?php

namespace Akeneo\Component\FileStorage\Formater;

/**
 * Convert bytes into human readable value
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class BytesFormater
{
    /**
     * Format bytes into human readable value
     *
     * @param int  $bytes    The file size in bytes/octets
     * @param int  $decimals The number of decimals
     * @param bool $si       International System of Units or not
     *
     * @return string
     */
    public function formatBytes($bytes, $decimals = 2, $si = false)
    {
        $unit = $si ? 1000 : 1024;
        $kilobyte = $unit;
        $megabyte = $kilobyte * $unit;
        $gigabyte = $megabyte * $unit;
        $terabyte = $gigabyte * $unit;

        if (($bytes >= $kilobyte) && ($bytes < $megabyte)) {
            return round($bytes / $kilobyte, $decimals) . ($si ? ' K' : ' KB');
        }

        if (($bytes >= $megabyte) && ($bytes < $gigabyte)) {
            return round($bytes / $megabyte, $decimals) . ($si ? ' M' : ' MB');
        }

        if (($bytes >= $gigabyte) && ($bytes < $terabyte)) {
            return round($bytes / $gigabyte, $decimals) . ($si ? ' G' : ' GB');
        }

        if ($bytes >= $terabyte) {
            return round($bytes / $terabyte, $decimals) . ($si ? ' T' : ' TB');
        }

        return $bytes . ' B';
    }
}
