<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operator;

use JMS\Serializer\Annotation\Exclude;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\ProductMassEditOperation;

/**
 * A batch operation operator
 * Applies batch operations to products passed in the form of QueryBuilder
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @Exclude
 */
class ProductMassEditOperator extends AbstractMassEditOperator
{
    protected $manager;

    /**
     * @param SecurityFacade $securityFacade
     * @param ProductManager $manager
     */
    public function __construct(SecurityFacade $securityFacade, ProductManager $manager)
    {
        parent::__construct($securityFacade);

        $this->manager = $manager;
    }

    public function getName()
    {
        return 'product';
    }

    /**
     * Finalize the batch operation - flush the products
     */
    public function finalizeOperation()
    {
        set_time_limit(0);

        $products = $this->operation->getObjectsToMassEdit();

        if ($this->operation instanceof ProductMassEditOperation) {
            $this->manager->saveAll($products, false, true, $this->operation->affectsCompleteness());
        }
    }
}
