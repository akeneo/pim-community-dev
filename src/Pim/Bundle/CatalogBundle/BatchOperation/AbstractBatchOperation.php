<?php

namespace Pim\Bundle\CatalogBundle\BatchOperation;

/**
 * Class that Batch operations might extends for convenience purpose
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractBatchOperation implements BatchOperation
{
    /**
     * {@inheritdoc}
     */
    public function getFormOptions()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $products)
    {
    }
}
