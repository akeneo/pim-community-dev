<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Extract;
/**
 * Interface extractable
 * TODO: should be moved in dataflow ?
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
interface ExtractInterface
{
    /**
     * Extract data from source (database tables, url, csv file, xml file, archive, ftp, etc)
     */
    public function extract();
}
