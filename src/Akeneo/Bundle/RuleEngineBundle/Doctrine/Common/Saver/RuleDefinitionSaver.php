<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Bundle\RuleEngineBundle\Doctrine\Common\Saver;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Component\StorageUtils\Saver\SavingOptionsResolverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Akeneo\Bundle\RuleEngineBundle\Event\BulkRuleEvent;
use Akeneo\Bundle\RuleEngineBundle\Event\RuleEvent;
use Akeneo\Bundle\RuleEngineBundle\Event\RuleEvents;
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

    /** @var SavingOptionsResolverInterface */
    protected $optionsResolver;

    /** @var string */
    protected $ruleDefinitionClass;

    /**
     * Constructor
     *
     * @param ObjectManager                  $objectManager
     * @param SavingOptionsResolverInterface $optionsResolver
     * @param EventDispatcherInterface       $eventDispatcher
     * @param string                         $ruleDefinitionClass
     */
    public function __construct(
        ObjectManager $objectManager,
        SavingOptionsResolverInterface $optionsResolver,
        EventDispatcherInterface $eventDispatcher,
        $ruleDefinitionClass
    ) {
        $this->ruleDefinitionClass = $ruleDefinitionClass;
        $this->eventDispatcher = $eventDispatcher;
        $this->objectManager = $objectManager;
        $this->optionsResolver = $optionsResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function save($ruleDefinition, array $options = [])
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

        $this->eventDispatcher->dispatch(RuleEvents::PRE_SAVE, new RuleEvent($ruleDefinition));

        $options = $this->optionsResolver->resolveSaveOptions($options);
        $this->objectManager->persist($ruleDefinition);
        if (true === $options['flush'] && true === $options['flush_only_object']) {
            $this->objectManager->flush($ruleDefinition);
        } elseif (true === $options['flush']) {
            $this->objectManager->flush();
        }

        $this->eventDispatcher->dispatch(RuleEvents::POST_SAVE, new RuleEvent($ruleDefinition));
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $ruleDefinitions, array $options = [])
    {
        $this->eventDispatcher->dispatch(RuleEvents::PRE_SAVE_ALL, new BulkRuleEvent($ruleDefinitions));

        $options = $this->optionsResolver->resolveSaveAllOptions($options);
        foreach ($ruleDefinitions as $ruleDefinition) {
            $this->save($ruleDefinition, ['flush' => false]);
        }

        if (true === $options['flush']) {
            $this->objectManager->flush();
        }

        $this->eventDispatcher->dispatch(RuleEvents::POST_SAVE_ALL, new BulkRuleEvent($ruleDefinitions));
    }
}
