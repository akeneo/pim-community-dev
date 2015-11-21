<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;

/**
 * Product manager
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be removed in 1.6
 */
class ProductManager implements ProductManagerInterface
{
    /** @var ObjectManager */
    protected $objectManager;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /**
     * Constructor
     *
     * @param ObjectManager                      $objectManager
     * @param ProductRepositoryInterface         $productRepository
     * @param AttributeRepositoryInterface       $attributeRepository
     */
    public function __construct(
        ObjectManager $objectManager,
        ProductRepositoryInterface $productRepository,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->objectManager = $objectManager;
        $this->productRepository = $productRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductRepository()
    {
        return $this->productRepository;
    }

    /**
     * Find a product by id
     * Also ensure that it contains all required values
     *
     * @param int $id
     *
     * @return ProductInterface|null
     */
    public function find($id)
    {
        $product = $this->getProductRepository()->findOneByWithValues($id);

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeRepository()
    {
        return $this->attributeRepository;
    }

    /**
     * Get object manager
     *
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }
}
