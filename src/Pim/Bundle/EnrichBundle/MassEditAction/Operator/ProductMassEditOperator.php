<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operator;

use Pim\Bundle\EnrichBundle\MassEditAction\Operation\ProductMassEditOperation;

/**
 * A batch operation operator, applies batch operations to products passed in the form of QueryBuilder
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductMassEditOperator extends AbstractMassEditOperator
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'product';
    }

    /**
     * {@inheritdoc}
     */
    public function getPerformedOperationRedirectionRoute()
    {
        return 'pim_enrich_product_index';
    }

    /**
     * {@inheritdoc}
     */
    public function finalizeOperation()
    {
        set_time_limit(0);
        if ($this->operation instanceof ProductMassEditOperation) {
            $this->operation->finalize();
        }
    }
}
