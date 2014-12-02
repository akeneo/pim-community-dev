<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Engine;

use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCondition;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueAction;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueAction;
use PimEnterprise\Bundle\RuleEngineBundle\Engine\BuilderInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvent;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvents;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Loads product rules.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ProductRuleBuilder implements BuilderInterface
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var string */
    protected $ruleClass;

    /** @var string */
    protected $setValueActionClass;

    /** @var string */
    protected $copyValueActionClass;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param string                   $ruleClass
     * @param string                   $setValueActionClass
     * @param string                   $copyValueActionClass
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        $ruleClass,
        $setValueActionClass,
        $copyValueActionClass
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->ruleClass = $ruleClass;
        $this->setValueActionClass = $setValueActionClass;
        $this->copyValueActionClass = $copyValueActionClass;

        $refClass = new \ReflectionClass($ruleClass);
        $interface = 'PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface';
        if (!$refClass->implementsInterface($interface)) {
            throw new \InvalidArgumentException(
                sprintf('The provided class name "%s" must implement interface "%s".', $ruleClass, $interface)
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function build(RuleDefinitionInterface $definition)
    {
        $this->eventDispatcher->dispatch(RuleEvents::PRE_LOAD, new RuleEvent($definition));

        /** @var \PimEnterprise\Bundle\RuleEngineBundle\Model\Rule $rule */
        $rule = new $this->ruleClass($definition);

        $content = json_decode($definition->getContent(), true);
        if (!array_key_exists('conditions', $content)) {
            throw new \LogicException(
                sprintf('Rule "%s" should have a "conditions" key in its content.', $definition->getCode())
            );
        }
        if (!array_key_exists('actions', $content)) {
            throw new \LogicException(
                sprintf('Rule "%s" should have a "actions" key in its content.', $definition->getCode())
            );
        }

        $this->loadConditions($rule, $content['conditions']);
        $this->loadActions($rule, $content['actions']);

        $this->eventDispatcher->dispatch(RuleEvents::POST_LOAD, new RuleEvent($definition));

        return $rule;
    }

    /**
     * Loads conditions into a rule.
     *
     * @param RuleInterface $rule
     * @param array         $rawConditions
     *
     * @return ProductRuleBuilder
     */
    protected function loadConditions(RuleInterface $rule, array $rawConditions)
    {
        $conditions = [];
        foreach ($rawConditions as $rawCondition) {
            //TODO: catch exception and log it ? or do not catch and totally fail ?
            $conditions[] = new ProductCondition($rawCondition);
        }

        $rule->setConditions($conditions);

        return $this;
    }

    /**
     * Loads actions into a rule.
     *
     * @param RuleInterface $rule
     * @param array         $rawActions
     *
     * @return ProductRuleBuilder
     */
    protected function loadActions(RuleInterface $rule, array $rawActions)
    {
        $actions = [];
        foreach ($rawActions as $rawAction) {
            if (!isset($rawAction['type'])) {
                // TODO: throw exception ? that should not happen? or simply log?
            } elseif (ProductSetValueAction::TYPE === $rawAction['type']) {
                $actions[] = new $this->setValueActionClass($rawAction);
            } elseif (ProductCopyValueAction::TYPE === $rawAction['type']) {
                $actions[] = new $this->copyValueActionClass($rawAction);
            }
        }

        $rule->setActions($actions);

        return $this;
    }
}
