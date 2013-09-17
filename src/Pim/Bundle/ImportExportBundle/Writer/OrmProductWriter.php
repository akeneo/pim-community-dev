<?php

namespace Pim\Bundle\ImportExportBundle\Writer;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\BatchBundle\Item\ItemWriterInterface;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;

/**
 * Product writer using ORM method
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OrmProductWriter extends AbstractConfigurableStepElement implements ItemWriterInterface
{
    /**
     * @var ProductManager
     */
    protected $productManager;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var Attribute
     */
    protected $identifierAttribute;

    /**
     * @param ProductManager $productManager Product manager
     * @param EntityManager  $entityManager  Doctrine's entity manager
     */
    public function __construct(ProductManager $productManager, EntityManager $entityManager)
    {
        $this->productManager = $productManager;
        $this->entityManager  = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        foreach ($items as $product) {
            $this->productManager->getStorageManager()->persist($product);
        }
        $this->productManager->getStorageManager()->flush();

        $this->productManager->getStorageManager()->clear('Pim\\Bundle\\CatalogBundle\\Entity\\ProductValue');
        $this->productManager->getStorageManager()->clear('Pim\\Bundle\\CatalogBundle\\Entity\\ProductPrice');
        $this->productManager->getStorageManager()->clear('Pim\\Bundle\\CatalogBundle\\Entity\\Product');
        $this->productManager->getStorageManager()->clear('Oro\\Bundle\\SearchBundle\\Entity\\Item');
        $this->productManager->getStorageManager()->clear('Oro\\Bundle\\SearchBundle\\Entity\\IndexText');
    }
}
