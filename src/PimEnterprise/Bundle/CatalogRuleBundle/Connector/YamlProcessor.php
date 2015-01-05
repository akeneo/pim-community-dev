<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Connector;

use Pim\Bundle\BaseConnectorBundle\Processor\DummyProcessor;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductConditionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Serializer\ProductRuleContentSerializerInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleDefinition;

/**
 * Processes and transform rules definition
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class YamlProcessor extends DummyProcessor
{
    /**
     * @var $serializer ProductRuleContentSerializerInterface
     */
    protected $serializer;

    /**
     * @param ProductRuleContentSerializerInterface $serializer
     */
    public function __construct(ProductRuleContentSerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $data = [];
        foreach ($item as $ruleDefinition) {
            /** @var $ruleDefinition RuleDefinition */
            if (null === $ruleDefinition) {
                return null;
            }
            $deserializedData = $this->serializer->deserialize($ruleDefinition->getContent());

            $conditions = [];
            $actions = [];

            $conditions = $this->normalizeConditions($deserializedData, $conditions);
            $actions = $this->normalizeActions($deserializedData, $actions);

            $data[$ruleDefinition->getCode()]['conditions'] = $conditions;
            $data[$ruleDefinition->getCode()]['actions'] = $actions;
        }

        return ['rules' => $data];
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [];
    }

    /**
     * Normalize conditions
     *
     * @param array $deserializedData
     * @param array $conditions
     *
     * @return array
     */
    protected function normalizeConditions(array $deserializedData, array $conditions)
    {
        foreach ($deserializedData['conditions'] as $conditionDefinition) {
            /** @var $conditionDefinition ProductConditionInterface */
            $condition = [
                'field' => $conditionDefinition->getField(),
                'operator' => $conditionDefinition->getOperator(),
                'value' => $conditionDefinition->getValue()
            ];

            if (null !== $conditionDefinition->getLocale()) {
                $condition = array_merge($condition, ['locale' => $conditionDefinition->getLocale()]);
            }

            if (null !== $conditionDefinition->getScope()) {
                $condition = array_merge($condition, ['scope' => $conditionDefinition->getScope()]);
            }

            $conditions[] = $condition;
        }

        return $conditions;
    }

    /**
     * Normalize actions
     *
     * @param array $deserializedData
     * @param array $actions
     *
     * @return array
     */
    protected function normalizeActions(array $deserializedData, array $actions)
    {
        foreach ($deserializedData['actions'] as $actionDefinition) {
            $action = [];
            if ($actionDefinition instanceof ProductCopyValueActionInterface) {
                $action = $this->normalizeCopyValueAction($actionDefinition);

            }

            if ($actionDefinition instanceof ProductSetValueActionInterface) {
                $action = $this->normalizeProductSetValueAction($actionDefinition);
            }

            ksort($action);
            $actions[] = $action;
        }

        return $actions;
    }

    /**
     * @param ProductCopyValueActionInterface $actionDefinition
     *
     * @return array
     */
    protected function normalizeCopyValueAction(ProductCopyValueActionInterface $actionDefinition)
    {
        $action = [
            'type'       => 'copy_value',
            'from_field' => $actionDefinition->getFromField(),
            'to_field'   => $actionDefinition->getToField(),
        ];

        if (null !== $actionDefinition->getFromLocale()) {
            $action = array_merge($action, ['from_locale' => $actionDefinition->getFromLocale()]);
            $action = array_merge($action, ['to_locale' => $actionDefinition->getToLocale()]);
        }

        if (null !== $actionDefinition->getFromScope()) {
            $action = array_merge($action, ['from_scope' => $actionDefinition->getFromScope()]);
            $action = array_merge($action, ['to_scope' => $actionDefinition->getToScope()]);
        }

        return $action;
    }

    /**
     * @param ProductSetValueActionInterface $actionDefinition
     *
     * @return array
     */
    protected function normalizeProductSetValueAction(ProductSetValueActionInterface $actionDefinition)
    {
        $action = [
            'type'  => 'set_value',
            'field' => $actionDefinition->getField(),
            'value' => $actionDefinition->getValue()
        ];

        if (null !== $actionDefinition->getLocale()) {
            $action = array_merge($action, ['locale' => $actionDefinition->getLocale()]);
        }

        if (null !== $actionDefinition->getScope()) {
            $action = array_merge($action, ['scope' => $actionDefinition->getScope()]);
        }

        return $action;
    }
}
