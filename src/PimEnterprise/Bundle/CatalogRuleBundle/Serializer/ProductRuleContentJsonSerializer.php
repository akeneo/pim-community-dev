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
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\ActionInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;

/**
 * Serialize and deserialize a product rule content that is stored in Json.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ProductRuleContentJsonSerializer implements ProductRuleContentSerializerInterface
{
    /** @var string */
    protected $conditionClass;
    
    /** @var string */
    protected $setValueActionClass;

    /** @var string */
    protected $copyValueActionClass;

    /**
     * @param string $conditionClass should implement \PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductConditionInterface
     * @param string $setValueActionClass should implement \PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface
     * @param string $copyValueActionClass should implement \PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface
     */
    public function __construct($conditionClass, $setValueActionClass, $copyValueActionClass)
    {
        $this->conditionClass = $conditionClass;
        $this->setValueActionClass = $setValueActionClass;
        $this->copyValueActionClass = $copyValueActionClass;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize(RuleInterface $rule)
    {
        $conditions = $actions = [];
        /** @var ProductConditionInterface $condition */
        foreach ($rule->getConditions() as $condition) {
            $conditions[] = $this->normalizeCondition($condition);
        }
        foreach ($rule->getActions() as $action) {
            if ($action instanceof ProductCopyValueActionInterface) {
                $actions[] = $this->normalizeCopyValueAction($action);
            } elseif ($action instanceof ProductSetValueActionInterface) {
                $actions[] = $this->normalizeSetValueAction($action);
            } else {
                throw new \LogicException(sprintf('Action of type "%s" can not be serialized.', get_class($action)));
            }
        }

        return json_encode([
            'conditions' => $conditions,
            'actions' => $actions,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function deserialize($content)
    {
        $decodedContent = json_decode($content, true);

        if (!array_key_exists('conditions', $decodedContent)) {
            throw new \LogicException(sprintf('Rule content "%s" should have a "conditions" key.', $content));
        } elseif (!array_key_exists('actions', $decodedContent)) {
            throw new \LogicException(sprintf('Rule content "%s" should have a "actions" key.', $content));
        }

        return [
            'conditions' => $this->denormalizeConditions($decodedContent['conditions']),
            'actions' => $this->denormalizeActions($decodedContent['actions'], $content),
        ];
    }

    /**
     * TODO: put this in the interface ?
     *
     * Build conditions of a content rule.
     *
     * @param array $rawConditions
     *
     * @return ProductConditionInterface[]
     */
    protected function denormalizeConditions(array $rawConditions)
    {
        $conditions = [];
        foreach ($rawConditions as $rawCondition) {
            $conditions[] = new $this->conditionClass($rawCondition);
        }

        return $conditions;
    }

    /**
     * TODO: put this in the interface ?
     *
     * Build actions of a content rule.
     *
     * @param array  $rawActions
     * @param string $strContent
     *
     * @return ActionInterface[] can be ProductSetValueActionInterface or ProductCopyValueActionInterface
     */
    protected function denormalizeActions(array $rawActions, $strContent)
    {
        $actions = [];
        foreach ($rawActions as $rawAction) {
            if (!isset($rawAction['type'])) {
                throw new \LogicException(
                    sprintf('Rule content "%s" has an action with no type.', $strContent)
                );
            } elseif (ProductSetValueActionInterface::TYPE === $rawAction['type']) {
                $actions[] = new $this->setValueActionClass($rawAction);
            } elseif (ProductCopyValueActionInterface::TYPE === $rawAction['type']) {
                $actions[] = new $this->copyValueActionClass($rawAction);
            } else {
                throw new \LogicException(
                    sprintf('Rule content "%s" has an unknown type of action "%s".', $strContent, $rawAction['type'])
                );
            }
        }

        return $actions;
    }

    /**
     * TODO: is it the right way to do it ?
     *
     * @param ProductConditionInterface $condition
     *
     * @return array
     */
    protected function normalizeCondition(ProductConditionInterface $condition)
    {
        $tmp = [];
        if (null !== $condition->getField()) {
            $tmp['field'] = $condition->getField();
        }
        if (null !== $condition->getOperator()) {
            $tmp['operator'] = $condition->getOperator();
        }
        if (null !== $condition->getValue()) {
            $tmp['value'] = $condition->getValue();
        }
        if (null !== $condition->getLocale()) {
            $tmp['locale'] = $condition->getLocale();
        }
        if (null !== $condition->getScope()) {
            $tmp['scope'] = $condition->getScope();
        }

        return $tmp;
    }

    /**
     * TODO: is it the right way to do it ?
     * @param ProductSetValueActionInterface $action
     *
     * @return array
     */
    protected function normalizeSetValueAction(ProductSetValueActionInterface $action)
    {
        $tmp = [];
        $tmp['type'] = ProductSetValueActionInterface::TYPE;

        if (null !== $action->getField()) {
            $tmp['field'] = $action->getField();
        }
        if (null !== $action->getValue()) {
            $tmp['value'] = $action->getValue();
        }
        if (null !== $action->getLocale()) {
            $tmp['locale'] = $action->getLocale();
        }
        if (null !== $action->getScope()) {
            $tmp['scope'] = $action->getScope();
        }

        return $tmp;
    }

    /**
     * TODO: is it the right way to do it ?
     * @param ProductCopyValueActionInterface $action
     *
     * @return array
     */
    protected function normalizeCopyValueAction(ProductCopyValueActionInterface $action)
    {
        $tmp = [];
        $tmp['type'] = ProductCopyValueActionInterface::TYPE;
        if (null !== $action->getFromField()) {
            $tmp['from_field'] = $action->getFromField();
        }
        if (null !== $action->getToField()) {
            $tmp['to_field'] = $action->getToField();
        }
        if (null !== $action->getFromLocale()) {
            $tmp['from_locale'] = $action->getFromLocale();
        }
        if (null !== $action->getToLocale()) {
            $tmp['to_locale'] = $action->getToLocale();
        }
        if (null !== $action->getFromScope()) {
            $tmp['from_scope'] = $action->getFromScope();
        }
        if (null !== $action->getToScope()) {
            $tmp['to_scope'] = $action->getToScope();
        }

        return $tmp;
    }
}
