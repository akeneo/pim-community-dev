<?php

namespace Pim\Bundle\CatalogBundle\Persistence;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Synchronize product with the database
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BasicPersister implements ProductPersister
{
    /** @var ObjectManager */
    protected $manager;

    /**
     * @param ManagerRegistry     $registry
     * @param CompletenessManager $completenessManager
     */
    public function __construct(ManagerRegistry $registry, CompletenessManager $completenessManager)
    {
        $this->registry            = $registry;
        $this->completenessManager = $completenessManager;
    }

    /**
     * {@inheritdoc}
     */
    public function persist(ProductInterface $product, array $options)
    {
        $options = array_merge(
            [
                'recalculate' => true,
                'flush' => true,
                'schedule' => true,
            ],
            $options
        );

        $manager = $this->registry->getManagerForClass(get_class($product));
        $manager->persist($product);

        if ($options['schedule'] || $options['recalculate']) {
            $this->completenessManager->schedule($product);
        }

        if ($options['recalculate'] || $options['flush']) {
            $manager->flush();
        }

        if ($options['recalculate']) {
            $this->completenessManager->generateMissingForProduct($product);
        }
    }
}
