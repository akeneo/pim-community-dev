<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Engine;

use Akeneo\Tool\Bundle\RuleEngineBundle\Engine\BuilderInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\RuleEvent;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\RuleEvents;
use Akeneo\Tool\Bundle\RuleEngineBundle\Exception\BuilderException;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

/**
 * Loads product rules.
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class ProductRuleBuilder implements BuilderInterface
{
    /** @var DenormalizerInterface */
    protected $chainedDenormalizer;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var string */
    protected $ruleClass;

    /**
     * @param DenormalizerInterface    $chainedDenormalizer
     * @param EventDispatcherInterface $eventDispatcher
     * @param string                   $ruleClass           should implement
     */
    public function __construct(
        DenormalizerInterface $chainedDenormalizer,
        EventDispatcherInterface $eventDispatcher,
        $ruleClass
    ) {
        $this->chainedDenormalizer = $chainedDenormalizer;
        $this->eventDispatcher = $eventDispatcher;
        $this->ruleClass = $ruleClass;
    }

    /**
     * {@inheritdoc}
     */
    public function build(RuleDefinitionInterface $definition)
    {
        $this->eventDispatcher->dispatch(new RuleEvent($definition), RuleEvents::PRE_BUILD);

        $rule = new $this->ruleClass($definition);

        try {
            $content = $this->chainedDenormalizer->denormalize(
                $definition->getContent(),
                $this->ruleClass,
                'rule_content'
            );
        } catch (\LogicException $e) {
            throw new BuilderException(
                sprintf('Impossible to build the rule "%s". %s', $definition->getCode(), $e->getMessage())
            );
        }

        $rule->setConditions($content['conditions']);
        $rule->setActions($content['actions']);

        $this->eventDispatcher->dispatch(new RuleEvent($definition), RuleEvents::POST_BUILD);

        return $rule;
    }
}
