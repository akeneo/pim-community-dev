<?php
namespace Pim\Bundle\ConnectorIcecatBundle\ETL\Read;

/**
 * Interface downloadable
 * TODO: should be moved in dataflow ?
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
interface DownloadInterface
{
    /**
     * Download file to defined path
     *
     * @param string $url  the url
     * @param string $path the path
     */
    public function download($url, $path);
}
