<?php

namespace Pim\Component\Connector\Writer\Doctrine;

/**
 * Custom writer for product associations to indicate the number of created/updated association targets
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAssociationWriter extends BaseWriter
{
    /**
     * {@inheritdoc}
     */
    protected function incrementCount(array $products)
    {
        foreach ($products as $product) {
            foreach ($product->getAssociations() as $association) {
                $count = count($association->getProducts()) + count($association->getGroups());

                $action = $association->getId() ? 'process' : 'create';

                for ($i = 0; $i < $count; $i++) {
                    $this->stepExecution->incrementSummaryInfo($action);
                }
            }
        }
    }
}
