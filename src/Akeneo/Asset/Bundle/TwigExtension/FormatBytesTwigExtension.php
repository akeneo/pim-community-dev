<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Bundle\TwigExtension;

use Akeneo\Tool\Component\FileStorage\Formater\BytesFormater;

/**
 * Convert bytes into human readable value
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class FormatBytesTwigExtension extends \Twig_Extension
{
    /**
     * Return functions registered as twig extensions
     *
     * @return array
     */
    public function getFilters()
    {
        return [
           new \Twig_SimpleFilter('formatBytes', [$this, 'formatBytes']),
        ];
    }

    /**
     * Format bytes into human readable value
     *
     * @param int  $bytes      The file size in bytes/octets
     * @param int  $decimals   The number of decimals
     * @param bool $intlSystem International System of Units or not
     *
     * @return string
     */
    public function formatBytes($bytes, $decimals = 2, $intlSystem = false)
    {
        $factor = $intlSystem ? 1000 : 1024;
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

        if (!$intlSystem || $bytes < $kilobyte) {
            $unit .= 'B';
        }

        return sprintf('%s %s', round($value, $decimals), $unit);
    }
}
