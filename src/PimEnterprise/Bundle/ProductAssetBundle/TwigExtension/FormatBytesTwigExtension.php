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
     * @param int $bytes
     * @param int $decimals
     *
     * @return string
     */
    public function formatBytes($bytes, $decimals = 2)
    {
        $kilobyte = 1024;
        $megabyte = $kilobyte * 1024;
        $gigabyte = $megabyte * 1024;
        $terabyte = $gigabyte * 1024;

        if (($bytes >= 0) && ($bytes < $kilobyte)) {
            return $bytes . ' B';
        } elseif (($bytes >= $kilobyte) && ($bytes < $megabyte)) {
            return round($bytes / $kilobyte, $decimals) . ' KB';
        } elseif (($bytes >= $megabyte) && ($bytes < $gigabyte)) {
            return round($bytes / $megabyte, $decimals) . ' MB';
        } elseif (($bytes >= $gigabyte) && ($bytes < $terabyte)) {
            return round($bytes / $gigabyte, $decimals) . ' GB';
        } elseif ($bytes >= $terabyte) {
            return round($bytes / $terabyte, $decimals) . ' TB';
        } else {
            return $bytes . ' B';
        }
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'twig_extension';
    }
}
