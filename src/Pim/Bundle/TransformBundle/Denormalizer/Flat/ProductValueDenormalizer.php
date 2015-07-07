<?php

namespace Pim\Bundle\TransformBundle\Denormalizer\Flat;

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

/**
 * Product value flat denormalizer
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueDenormalizer extends AbstractEntityDenormalizer
{
    /** @var ProductBuilder */
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
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        return $this->doDenormalize($data, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    protected function doDenormalize($data, $format, array $context)
    {
        if (isset($context['entity'])) {
            $value = $context['entity'];
            // TODO : this fix should be merged from EE 1.0.x PIM-3533
            unset($context['entity']);
            $context['value']  = $value;
        } else {
            throw new InvalidArgumentException('Value should be passed in context');
        }

        $attributeType = $value->getAttribute()->getAttributeType();
        $dataValue = $this->serializer->denormalize($data, $attributeType, $format, $context);
        if (null !== $dataValue) {
            $value->setData($dataValue);
        }

        return $value;
    }
}
