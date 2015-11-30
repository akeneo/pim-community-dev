<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Doctrine\Common\Persistence\ObjectManager;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;

/**
 * Product manager
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be removed in 1.6
 */
class ProductManager
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
     * @deprecated will be removed in 1.6
     *
     * @return ProductInterface|null
     */
    public function find($id)
    {
        $product = $this->productRepository->findOneByWithValues($id);

        return $product;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated will be removed in 1.6
     */
    public function getAttributeRepository()
    {
        return $this->attributeRepository;
    }

    /**
     * Get object manager
     *
     * @deprecated will be removed in 1.6
     *
     * @return ObjectManager
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }
}
