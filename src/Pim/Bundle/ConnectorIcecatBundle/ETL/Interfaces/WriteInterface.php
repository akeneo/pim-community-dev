<?php
namespace Pim\Bundle\ConnectorIcecatBundle\ETL\Interfaces;
/**
 * Interface writable
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
interface WriteInterface
{
    /**
     * Write data with batch size
     * @param array   $data      data to write
     * @param integer $batchSize number of object persist in a transaction
     */
    public function write($data, $batchSize = 200);
}
