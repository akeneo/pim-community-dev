<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\TwigExtension;

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

    /**
     * @return string
     */
    public function getName()
    {
        return 'twig_extension';
    }
}
