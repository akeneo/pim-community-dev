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

use Akeneo\Component\FileStorage\Formater\BytesFormater;

/**
 * Convert bytes into human readable value
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class FormatBytesTwigExtension extends \Twig_Extension
{
    /** @var BytesFormater */
    protected $bytesFormater;

    /**
     * @param BytesFormater $bytesFormater
     */
    public function __construct(BytesFormater $bytesFormater)
    {
        $this->bytesFormater = $bytesFormater;
    }

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
        return $this->bytesFormater->formatBytes($bytes, $decimals, $intlSystem);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'twig_extension';
    }
}
