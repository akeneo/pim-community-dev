<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Normalizer\ProductRule;

use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize product set value rule actions.
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class SetValueActionNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        $data = [];
        $data['type'] = ProductSetValueActionInterface::TYPE;

        if (null !== $object->getField()) {
            $data['field'] = $object->getField();
        }
        if (null !== $object->getValue()) {
            $data['value'] = $object->getValue();
        }
        if (null !== $object->getLocale()) {
            $data['locale'] = $object->getLocale();
        }
        if (null !== $object->getScope()) {
            $data['scope'] = $object->getScope();
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductSetValueActionInterface;
    }
}
