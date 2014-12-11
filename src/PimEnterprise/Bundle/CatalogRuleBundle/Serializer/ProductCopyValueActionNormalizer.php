<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Serializer;

use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize and denormalize product copy value rule actions.
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class ProductCopyValueActionNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /** @var string */
    protected $copyValueActionClass;

    /**
     * @param string $copyValueActionClass should implement
     *                                     \PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface
     */
    public function __construct($copyValueActionClass)
    {
        $this->copyValueActionClass = $copyValueActionClass;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $data = [];
        $data['type'] = ProductCopyValueActionInterface::TYPE;
        if (null !== $object->getFromField()) {
            $data['from_field'] = $object->getFromField();
        }
        if (null !== $object->getToField()) {
            $data['to_field'] = $object->getToField();
        }
        if (null !== $object->getFromLocale()) {
            $data['from_locale'] = $object->getFromLocale();
        }
        if (null !== $object->getToLocale()) {
            $data['to_locale'] = $object->getToLocale();
        }
        if (null !== $object->getFromScope()) {
            $data['from_scope'] = $object->getFromScope();
        }
        if (null !== $object->getToScope()) {
            $data['to_scope'] = $object->getToScope();
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductCopyValueActionInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        return new $this->copyValueActionClass($data);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === $this->copyValueActionClass && isset($data['type'])
               && ProductCopyValueActionInterface::TYPE === $data['type'];
    }
}
