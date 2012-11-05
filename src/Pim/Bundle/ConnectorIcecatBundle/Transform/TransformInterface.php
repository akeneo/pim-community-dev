<?php
namespace Pim\Bundle\ConnectorIcecatBundle\Transform;

/**
 *
 * Interface transform
 * TODO: should be moved in dataflow ?
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
interface TransformInterface
{
    /**
     * Transform data xml to array, csv to xml, etc
     */
    public function transform();
}