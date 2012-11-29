<?php
namespace Pim\Bundle\ConnectorIcecatBundle\ETL\Read;

/**
 * Interface to unpack a file.
 * TODO: should be moved in dataflow ?
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
interface UnpackInterface
{
    /**
     * Unpack file archived to file
     *
     * @param string $archive the archive
     * @param string $file    the extracted file path
     */
    public function unpack($archive, $file);
}
