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
use PimEnterprise\Bundle\RuleEngineBundle\Model\LoadedRule;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Loads product rules.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ProductRuleLoader implements LoaderInterface
{
    /** @var EventDispatcher */
    protected $eventDispatcher;

    /**
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct(EventDispatcher $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function load(RuleInterface $rule)
    {
        $this->eventDispatcher->dispatch(RuleEvents::PRE_LOAD, new RuleEvent($rule));

        //TODO: do not hardcode this
        $loaded = new LoadedRule($rule);

        $content = json_decode($rule->getContent(), true);
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
