<?php

namespace Pim\Bundle\BaseConnectorBundle\Writer\Doctrine;

/**
 * Custom writer for product associations to indicate the number of created/updated association targets
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be removed in 1.6. Please use \Pim\Component\Connector\Writer\Doctrine\ProductAssociationWriter
 */
class ProductAssociationWriter extends Writer
{
    /**
     * @param object $item
     */
    protected function incrementCount($item)
    {
        $count = count($item->getProducts()) + count($item->getGroups());

        $action = (null !== $item->getId()) ? 'process' : 'create';

        for ($i = 0; $i < $count; $i++) {
            $this->stepExecution->incrementSummaryInfo($action);
        }
    }
}
