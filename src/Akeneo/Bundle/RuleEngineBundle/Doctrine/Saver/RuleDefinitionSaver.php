<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Bundle\RuleEngineBundle\Doctrine\Saver;

use Akeneo\Component\Persistence\BulkSaverInterface;
use Akeneo\Component\Persistence\SaverInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\Saver\BaseSavingOptionsResolver;
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

    /** @var BaseSavingOptionsResolver */
    protected $optionsResolver;

    /** @var string */
    protected $ruleDefinitionClass;

    /**
     * Constructor
     *
     * @param ObjectManager             $objectManager
     * @param BaseSavingOptionsResolver $optionsResolver
     * @param EventDispatcherInterface  $eventDispatcher
     * @param string                    $ruleDefinitionClass
     */
    public function __construct(
        ObjectManager $objectManager,
        BaseSavingOptionsResolver $optionsResolver,
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
    public function save($object, array $options = [])
    {
        if (!$object instanceof $this->ruleDefinitionClass) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "%s", "%s" provided.',
                    $this->ruleDefinitionClass,
                    ClassUtils::getClass($object)
                )
            );
        }

        $this->eventDispatcher->dispatch(RuleEvents::PRE_SAVE, new RuleEvent($object));

        $options = $this->optionsResolver->resolveSaveOptions($options);
        $this->objectManager->persist($object);
        if (true === $options['flush'] && true === $options['flush_only_object']) {
            $this->objectManager->flush($object);
        } elseif (true === $options['flush']) {
            $this->objectManager->flush();
        }

        $this->eventDispatcher->dispatch(RuleEvents::POST_SAVE, new RuleEvent($object));
    }

    /**
     * {@inheritdoc}
     */
    public function saveAll(array $objects, array $options = [])
    {
        $this->eventDispatcher->dispatch(RuleEvents::PRE_SAVE_ALL, new BulkRuleEvent($objects));

        $options = $this->optionsResolver->resolveSaveAllOptions($options);
        foreach ($objects as $object) {
            $this->save($object, ['flush' => false]);
        }

        if (true === $options['flush']) {
            $this->objectManager->flush();
        }

        $this->eventDispatcher->dispatch(RuleEvents::POST_SAVE_ALL, new BulkRuleEvent($objects));
    }
}
