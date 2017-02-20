<?php

namespace Pim\Bundle\VersioningBundle\Denormalizer\Flat;

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
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
    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /**
     * @param ManagerRegistry         $managerRegistry
     * @param string                  $entityClass
     * @param ProductBuilderInterface $productBuilder
     */
    public function __construct(ManagerRegistry $managerRegistry, $entityClass, ProductBuilderInterface $productBuilder = null)
    {
        parent::__construct($managerRegistry, $entityClass);

        $this->productBuilder = $productBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
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
            $context['value'] = $value;
        } else {
            throw new InvalidArgumentException('Value should be passed in context');
        }

        $attributeType = $value->getAttribute()->getType();
        $dataValue = $this->serializer->denormalize($data, $attributeType, $format, $context);
        if (null !== $dataValue) {
            $value->setData($dataValue);
        }

        return $value;
    }
}
