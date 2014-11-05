<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductRuleBundle\Engine;

use PimEnterprise\Bundle\RuleEngineBundle\Engine\LoaderInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvent;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvents;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
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

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param                          $loadedRuleClass
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, $loadedRuleClass)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->loadedRuleClass = $loadedRuleClass;

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

        $loaded->setConditions($content['conditions']);
        $loaded->setActions($content['actions']);

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
}
