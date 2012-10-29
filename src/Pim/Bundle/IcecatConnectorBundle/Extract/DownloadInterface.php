<?php
namespace Pim\Bundle\IcecatConnectorBundle\Extract;

/**
 *
 * Interface downloadable
 * TODO: should be moved in dataflow ?
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
interface DownloadInterface
{
    /**
     * Download file in defined path
     * @param string $url
     * @param string $path
     */
    public function download($url, $file);
}