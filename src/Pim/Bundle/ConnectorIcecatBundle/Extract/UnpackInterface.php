<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Extract;

/**
 *
 * Interface to unpack a file.
 * TODO: should be moved in dataflow ?
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
interface UnpackInterface
{
    /**
     * Unpack file archived to file
     * @param string $url
     * @param string $file
     */
    public function unpack($archivedFile, $file);
}