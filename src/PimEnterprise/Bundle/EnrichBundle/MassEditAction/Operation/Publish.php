<?php

namespace PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operation;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\ProductMassEditOperation;
use PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager;

/**
 * Batch operation to publish products
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class Publish extends ProductMassEditOperation
{
    /**
     * @var PublishedProductManager
     */
    protected $manager;

    /**
     * @param PublishedProductManager $manager
     */
    public function __construct(PublishedProductManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return 'pimee_enrich_mass_publish';
    }

    /**
     * {@inheritdoc}
     */
    protected function doPerform(ProductInterface $product)
    {
        $this->manager->publish($product);
    }
}
