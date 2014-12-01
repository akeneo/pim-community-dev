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
use PimEnterprise\Bundle\RuleEngineBundle\Engine\LoaderInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvent;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvents;
use PimEnterprise\Bundle\RuleEngineBundle\Model\LoadedRuleInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Loads product rules.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ProductRuleLoader implements LoaderInterface
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var string */
    protected $loadedRuleClass;

    /** @var string */
    protected $setValueActionClass;

    /** @var string */
    protected $copyValueActionClass;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param string                   $loadedRuleClass
     * @param string                   $setValueActionClass
     * @param string                   $copyValueActionClass
     */
    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        $loadedRuleClass,
        $setValueActionClass,
        $copyValueActionClass
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->loadedRuleClass = $loadedRuleClass;
        $this->setValueActionClass = $setValueActionClass;
        $this->copyValueActionClass = $copyValueActionClass;

        $refClass = new \ReflectionClass($loadedRuleClass);
        $interface = 'PimEnterprise\Bundle\RuleEngineBundle\Model\LoadedRuleInterface';
        if (!$refClass->implementsInterface($interface)) {
            throw new \InvalidArgumentException(
                sprintf('The provided class name "%s" must implement interface "%s".', $loadedRuleClass, $interface)
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load(RuleInterface $rule)
    {
        $this->eventDispatcher->dispatch(RuleEvents::PRE_LOAD, new RuleEvent($rule));

        /** @var \PimEnterprise\Bundle\RuleEngineBundle\Model\LoadedRule $loaded */
        $loaded = new $this->loadedRuleClass($rule);

        $content = json_decode($rule->getContent(), true);
        if (!array_key_exists('conditions', $content)) {
            throw new \LogicException(sprintf('Rule "%s" should have a "conditions" key in its content.', $rule->getCode()));
        }
        if (!array_key_exists('actions', $content)) {
            throw new \LogicException(sprintf('Rule "%s" should have a "actions" key in its content.', $rule->getCode()));
        }

        $this->loadConditions($loaded, $content['conditions']);
        $this->loadActions($loaded, $content['actions']);

        $this->eventDispatcher->dispatch(RuleEvents::POST_LOAD, new RuleEvent($rule));

        return $loaded;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(RuleInterface $rule)
    {
        return 'product' === $rule->getType();
    }

    /**
     * Loads conditions into a rule.
     *
     * @param LoadedRuleInterface $rule
     * @param array               $rawConditions
     *
     * @return ProductRuleLoader
     */
    protected function loadConditions(LoadedRuleInterface $rule, array $rawConditions)
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
     * @param LoadedRuleInterface $rule
     * @param array               $rawActions
     *
     * @return ProductRuleLoader
     */
    protected function loadActions(LoadedRuleInterface $rule, array $rawActions)
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
