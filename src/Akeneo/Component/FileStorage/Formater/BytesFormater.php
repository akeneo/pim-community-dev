<?php

namespace Akeneo\Component\FileStorage\Formater;

/**
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BytesFormater
{
    /**
     * Format bytes into human readable value
     *
     * @param int  $bytes    The file size in bytes/octets
     * @param int  $decimals The number of decimals
     * @param bool $isSi     International System of Units or not
     *
     * @return string
     */
    public function formatBytes($bytes, $decimals = 2, $isSi = false)
    {
        $factor = $isSi ? 1000 : 1024;
        $kilobyte = $factor;
        $megabyte = $kilobyte * $factor;
        $gigabyte = $megabyte * $factor;
        $terabyte = $gigabyte * $factor;

        $value = $bytes;
        $unit = '';

        if ($bytes >= $kilobyte) {
            $value = $bytes / $kilobyte;
            $unit = 'K';
        }
        if ($bytes >= $megabyte) {
            $value = $bytes / $megabyte;
            $unit = 'M';
        }
        if ($bytes >= $gigabyte) {
            $value = $bytes / $gigabyte;
            $unit = 'G';
        }
        if ($bytes >= $terabyte) {
            $value = $bytes / $terabyte;
            $unit = 'T';
        }

        if (!$isSi || $bytes < $kilobyte) {
            $unit .= 'B';
        }

        return $this->format($value, $decimals, $unit);
    }

    /**
     * @param float  $value
     * @param int    $decimals
     * @param string $unit
     *
     * @return string
     */
    protected function format($value, $decimals, $unit)
    {
        return sprintf('%s %s', round($value, $decimals), $unit);
    }
}
