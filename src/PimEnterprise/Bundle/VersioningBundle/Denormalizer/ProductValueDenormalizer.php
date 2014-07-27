<?php

namespace PimEnterprise\Bundle\VersioningBundle\Denormalizer;

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
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
    public function __construct(ManagerRegistry $managerRegistry, $entityClass, ProductBuilder $productBuilder = null)
    {
        parent::__construct($managerRegistry, $entityClass);

        $this->productBuilder = $productBuilder;
    }

    /**
     * {@inheritdoc]
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $value = $context['entity'];
        $context['entity'] = null;
        $context['value']  = $value;

        // Call denormalizer with attribute type
        $dataValue = $this->serializer->denormalize($data, $value->getAttribute()->getAttributeType(), 'csv', $context);
        if (null !== $dataValue) {
            $value->setData($dataValue);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    protected function doDenormalize(array $data, $format, array $context)
    {
        throw new \Exception('Should not be called');
    }
}
