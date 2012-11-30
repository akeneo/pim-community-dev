<?php
namespace Pim\Bundle\ConnectorIcecatBundle\ETL\Interfaces;

/**
 * Interface enrich
 * TODO: should be moved in dataflow ?
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
interface EnrichInterface
{
    /**
     * Transform data xml to array, csv to xml, etc
     */
    public function enrich();
}
