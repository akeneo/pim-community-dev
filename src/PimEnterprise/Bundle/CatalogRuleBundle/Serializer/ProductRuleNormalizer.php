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
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize product rules.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProductRuleNormalizer implements NormalizerInterface
{
    /** @var ProductRuleConditionNormalizer */
    protected $conditionNormalizer;

    /** @var ProductSetValueActionNormalizer */
    protected $setActionNormalizer;

    /** @var ProductCopyValueActionNormalizer */
    protected $copyActionNormalizer;

    /** @var ProductRuleContentSerializerInterface */
    protected $serializer;

    /**
     * @param ProductRuleContentSerializerInterface $serializer
     * @param NormalizerInterface                   $conditionNormalizer
     * @param NormalizerInterface                   $setActionNormalizer
     * @param NormalizerInterface                   $copyActionNormalizer
     */
    public function __construct(
        ProductRuleContentSerializerInterface $serializer,
        NormalizerInterface $conditionNormalizer,
        NormalizerInterface $setActionNormalizer,
        NormalizerInterface $copyActionNormalizer
    ) {
        $this->serializer           = $serializer;
        $this->conditionNormalizer  = $conditionNormalizer;
        $this->setActionNormalizer  = $setActionNormalizer;
        $this->copyActionNormalizer = $copyActionNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        /** @var $object RuleDefinitionInterface */
        $ruleContent = $this->serializer->deserialize($object->getContent());

        $conditions = $this->normalizeConditions($ruleContent);
        $actions    = $this->normalizeActions($object, $ruleContent);

        return ['conditions' => $conditions, 'actions' => $actions];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof RuleDefinitionInterface && $data->getType() === 'product';
    }

    /**
     * Normalize conditions
     *
     * @param array $ruleContent
     *
     * @return array
     */
    protected function normalizeConditions(array $ruleContent)
    {
        $conditions = [];
        foreach ($ruleContent['conditions'] as $conditionDefinition) {
            $conditions[] = $this->conditionNormalizer->normalize($conditionDefinition);
        }

        return $conditions;
    }

    /**
     * Normalize actions
     *
     * @param RuleDefinitionInterface $ruleDefinition
     * @param array                   $ruleContent
     *
     * @return array
     */
    protected function normalizeActions(RuleDefinitionInterface $ruleDefinition, array $ruleContent)
    {
        $actions = [];
        /** @var $actionDefinition RuleDefinitionInterface */
        foreach ($ruleContent['actions'] as $actionDefinition) {
            if ($actionDefinition instanceof ProductCopyValueActionInterface) {
                $action = $this->copyActionNormalizer->normalize($actionDefinition);
            } elseif ($actionDefinition instanceof ProductSetValueActionInterface) {
                $action = $this->setActionNormalizer->normalize($actionDefinition);
            } else {
                throw new \LogicException(
                    sprintf(
                        'Rule "%s" has an unknown type of action.',
                        $ruleDefinition->getCode()
                    )
                );
            }

            $actions[] = $action;
        }

        return $actions;
    }
}
