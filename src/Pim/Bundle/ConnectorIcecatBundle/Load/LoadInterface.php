<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Load;
/**
 *
 * Interface load
 * TODO: should be moved in dataflow ?
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
interface LoadInterface
{
    /**
     * Load data (write) to database, file, etc
     */
    public function load();
}