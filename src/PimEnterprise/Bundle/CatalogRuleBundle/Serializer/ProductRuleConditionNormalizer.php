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

use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductConditionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize and denormalize product rule conditions.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ProductRuleConditionNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /** @var string */
    protected $conditionClass;

    /**
     * @param string $conditionClass should implement
     *                               \PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductConditionInterface
     */
    public function __construct($conditionClass)
    {
        $this->conditionClass = $conditionClass;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        return new $this->conditionClass($data);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        //TODO: use $conditionClass
        return $type === 'PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCondition';
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($condition, $format = null, array $context = [])
    {
        $data = [];
        if (null !== $condition->getField()) {
            $data['field'] = $condition->getField();
        }
        if (null !== $condition->getOperator()) {
            $data['operator'] = $condition->getOperator();
        }
        if (null !== $condition->getValue()) {
            $data['value'] = $condition->getValue();
        }
        if (null !== $condition->getLocale()) {
            $data['locale'] = $condition->getLocale();
        }
        if (null !== $condition->getScope()) {
            $data['scope'] = $condition->getScope();
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductConditionInterface;
    }
}
