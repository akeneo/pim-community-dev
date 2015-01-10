<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Denormalizer\ProductRule;

use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Denormalize a product rule content.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ContentDenormalizer implements DenormalizerInterface
{
    /** @var DenormalizerInterface */
    protected $conditionNormalizer;

    /** @var DenormalizerInterface */
    protected $setValueNormalizer;

    /** @var DenormalizerInterface */
    protected $copyValueNormalizer;

    /** @var string */
    protected $ruleClass;

    /**
     * @param DenormalizerInterface $conditionNormalizer
     * @param DenormalizerInterface $setValueNormalizer
     * @param DenormalizerInterface $copyValueNormalizer
     * @param string                $ruleClass
     */
    public function __construct(
        DenormalizerInterface $conditionNormalizer,
        DenormalizerInterface $setValueNormalizer,
        DenormalizerInterface $copyValueNormalizer,
        $ruleClass
    ) {
        $this->conditionNormalizer = $conditionNormalizer;
        $this->setValueNormalizer = $setValueNormalizer;
        $this->copyValueNormalizer = $copyValueNormalizer;
        $this->ruleClass = $ruleClass;
    }

    /**
     * Denormalizes a rule content.
     *
     * {@inheritdoc}
     *
     * @return array with keys
     *                  "conditions" which is an array of ConditionInterface
     *                  "actions" which is an array of ConditionInterface
     */
    public function denormalize($ruleContent, $class, $format = null, array $context = array())
    {
        // this check is performed this denormalizer is not used via the serializer
        if (!$this->supportsDenormalization($ruleContent, $class, $format)) {
            throw new \InvalidArgumentException(
                sprintf('Rule content "%s" can not be denormalized.', json_encode($ruleContent))
            );
        }

        $conditions = $actions = [];

        foreach ($ruleContent['conditions'] as $condition) {
            $conditions[] = $this->conditionNormalizer->denormalize($condition, $class, $format, $context);
        }

        foreach ($ruleContent['actions'] as $action) {
            if (!isset($action['type'])) {
                throw new \LogicException(
                    sprintf('Rule content "%s" has an action with no type.', json_encode($ruleContent))
                );
            } elseif (ProductSetValueActionInterface::TYPE === $action['type']) {
                $actions[] = $this->setValueNormalizer->denormalize($action, $class, $format, $context);
            } elseif (ProductCopyValueActionInterface::TYPE === $action['type']) {
                $actions[] = $this->copyValueNormalizer->denormalize($action, $class, $format, $context);
            } else {
                throw new \LogicException(
                    sprintf(
                        'Rule content "%s" has an unknown type of action "%s".',
                        json_encode($ruleContent),
                        $action['type']
                    )
                );
            }
        }

        return [
            'conditions' => $conditions,
            'actions' => $actions,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($ruleContent, $type, $format = null)
    {
        /*
        if ($this->ruleClass === $type && empty($ruleContent)) {
            return true;
        }
        */

        return $this->ruleClass === $type &&
            isset($ruleContent['conditions']) &&
            is_array($ruleContent['conditions']) &&
            isset($ruleContent['actions']) &&
            is_array($ruleContent['actions'])
        ;
    }
}
