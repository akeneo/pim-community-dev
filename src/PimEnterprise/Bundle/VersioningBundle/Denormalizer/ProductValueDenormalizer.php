<?php

namespace PimEnterprise\Bundle\VersioningBundle\Denormalizer;

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductValueDenormalizer extends AbstractEntityDenormalizer
{
    /** @var \Pim\Bundle\CatalogBundle\Builder\ProductBuilder */
    protected $productBuilder;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param string          $entityClass
     * @param ProductBuilder  $productBuilder
     */
    public function __construct(ManagerRegistry $managerRegistry, $entityClass, ProductBuilder $productBuilder)
    {
        parent::__construct($managerRegistry, $entityClass);

        $this->productBuilder = $productBuilder;
    }

    /**
     * {@inheritdoc]
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $value = isset($context['entity'])
            ? $context['entity']
            : $this->getProductValue($context['attribute_code'], $context['product']);

        $value->setData($data);
    }

    protected function getProductValue($identifier, ProductInterface $product)
    {
        $attribute = $this->getAttribute($identifier);

        $this->productBuilder->addAttributeToProduct($product, $attribute);

        return $product->getValue($identifier);
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getProductRepository()
    {
        return $this->managerRegistry->getRepository('Pim\Bundle\CatalogBundle\Model\Product');
    }

    protected function getAttributeRepository()
    {
        return $this->managerRegistry->getRepository('Pim\Bundle\CatalogBundle\Entity\Attribute');
    }

    protected function getAttribute($attributeCode)
    {
        return $this->getAttributeRepository()->findByReference($attributeCode);
    }
}
