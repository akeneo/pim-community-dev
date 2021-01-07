<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Bundle\RuleEngineBundle\Doctrine\Common\Saver;

use Akeneo\Tool\Bundle\RuleEngineBundle\Event\BulkRuleEvent;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\RuleEvent;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\RuleEvents;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Rule definition saver
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class RuleDefinitionSaver implements SaverInterface, BulkSaverInterface
{
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var ObjectManager */
    protected $objectManager;

    /** @var string */
    protected $ruleDefinitionClass;

    /**
     * Constructor
     *
     * @param ObjectManager                  $objectManager
     * @param EventDispatcherInterface       $eventDispatcher
     * @param string                         $ruleDefinitionClass
     */
    public function __construct(
        ObjectManager $objectManager,
        EventDispatcherInterface $eventDispatcher,
        $ruleDefinitionClass
    ) {
        $this->objectManager = $objectManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->ruleDefinitionClass = $ruleDefinitionClass;
    }

    /**
     * {@inheritdoc}
     */
    public function save($ruleDefinition, array $options = [])
    {
        $this->validateRuleDefinition($ruleDefinition);
        $this->eventDispatcher->dispatch(new RuleEvent($ruleDefinition), RuleEvents::PRE_SAVE);
        $this->objectManager->persist($ruleDefinition);
        $this->objectManager->flush();
        $this->eventDispatcher->dispatch(new RuleEvent($ruleDefinition), RuleEvents::POST_SAVE);
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $ruleDefinitions, array $options = [])
    {
        $this->eventDispatcher->dispatch(new BulkRuleEvent($ruleDefinitions), RuleEvents::PRE_SAVE_ALL);
        foreach ($ruleDefinitions as $ruleDefinition) {
            $this->validateRuleDefinition($ruleDefinition);
            $this->eventDispatcher->dispatch(new RuleEvent($ruleDefinition), RuleEvents::PRE_SAVE);
            $this->objectManager->persist($ruleDefinition);
        }
        $this->objectManager->flush();
        foreach ($ruleDefinitions as $ruleDefinition) {
            $this->eventDispatcher->dispatch(new RuleEvent($ruleDefinition), RuleEvents::POST_SAVE);
        }
        $this->eventDispatcher->dispatch(new BulkRuleEvent($ruleDefinitions), RuleEvents::POST_SAVE_ALL);
    }

    /**
     * @param object $ruleDefinition
     *
     * @throws \InvalidArgumentException
     */
    protected function validateRuleDefinition($ruleDefinition)
    {
        if (!$ruleDefinition instanceof $this->ruleDefinitionClass) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "%s", "%s" provided.',
                    $this->ruleDefinitionClass,
                    ClassUtils::getClass($ruleDefinition)
                )
            );
        }
    }
}
