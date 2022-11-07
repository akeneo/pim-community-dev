<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Bundle\EventSubscriber;

use Akeneo\Pim\Automation\RuleEngine\Bundle\Manager\RuleRelationManager;
use Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleBuilder;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\BulkRuleEvent;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\RuleEvent;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\RuleEvents;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleRelationRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Rule relations subscriber
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class RuleRelationSubscriber implements EventSubscriberInterface
{
    /** @var RuleRelationManager */
    protected $ruleRelationManager;

    /** @var BulkSaverInterface */
    protected $ruleRelationSaver;

    /** @var BulkRemoverInterface */
    protected $ruleRelationRemover;

    /** @var RuleRelationRepositoryInterface */
    protected $ruleRelationRepo;

    /** @var ProductRuleBuilder */
    protected $productRuleBuilder;

    /**
     * Constructor
     *
     * @param RuleRelationManager             $ruleRelationManager
     * @param BulkSaverInterface              $ruleRelationSaver
     * @param BulkRemoverInterface            $ruleRelationRemover
     * @param RuleRelationRepositoryInterface $ruleRelationRepo
     * @param ProductRuleBuilder              $productRuleBuilder
     */
    public function __construct(
        RuleRelationManager $ruleRelationManager,
        BulkSaverInterface $ruleRelationSaver,
        BulkRemoverInterface $ruleRelationRemover,
        RuleRelationRepositoryInterface $ruleRelationRepo,
        ProductRuleBuilder $productRuleBuilder
    ) {
        $this->ruleRelationManager = $ruleRelationManager;
        $this->ruleRelationSaver = $ruleRelationSaver;
        $this->ruleRelationRemover = $ruleRelationRemover;
        $this->ruleRelationRepo = $ruleRelationRepo;
        $this->productRuleBuilder = $productRuleBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_REMOVE    => 'removeAttribute',
            RuleEvents::POST_SAVE        => 'saveRule',
            RuleEvents::POST_SAVE_ALL    => 'saveRules'
        ];
    }

    /**
     * Deletes a rule relation
     *
     * @param GenericEvent $event
     */
    public function removeAttribute(GenericEvent $event)
    {
        $entity = $event->getSubject();

        if (!$entity instanceof AttributeInterface) {
            return;
        }

        $ruleRelations = $this->ruleRelationRepo
            ->findBy(['resourceId' => $entity->getId(), 'resourceName' => ClassUtils::getClass($entity)]);

        $this->ruleRelationRemover->removeAll($ruleRelations);
    }

    /**
     * When saves a single rule
     *
     * @param RuleEvent $event
     */
    public function saveRule(RuleEvent $event)
    {
        $definition = $event->getDefinition();
        $this->saveRuleRelations($definition);
    }

    /**
     * When saves many rules, via import for instance
     *
     * @param BulkRuleEvent $event
     */
    public function saveRules(BulkRuleEvent $event)
    {
        $definitions = $event->getDefinitions();
        foreach ($definitions as $definition) {
            $this->saveRuleRelations($definition);
        }
    }

    /**
     * Saves a rule relation
     *
     * @param RuleDefinitionInterface $definition
     */
    protected function saveRuleRelations(RuleDefinitionInterface $definition)
    {
        if (null === $definition->getId()) {
            return;
        }

        $this->removeRuleRelations($definition);
        $this->addRuleRelations($definition);
    }

    /**
     * @param RuleDefinitionInterface $definition
     */
    protected function addRuleRelations(RuleDefinitionInterface $definition)
    {
        $rule = $this->productRuleBuilder->build($definition);
        $relatedElements = $this->ruleRelationManager->getImpactedElements($rule);

        $ruleRelations = [];
        $className = $this->ruleRelationRepo->getClassName();
        foreach ($relatedElements as $relatedElement) {
            $ruleRelation = new $className();
            $ruleRelation->setRuleDefinition($definition);
            $ruleRelation->setResourceName(ClassUtils::getClass($relatedElement));
            $ruleRelation->setResourceId($relatedElement->getId());

            $ruleRelations[] = $ruleRelation;
        }

        $this->ruleRelationSaver->saveAll($ruleRelations);
    }

    /**
     * @param RuleDefinitionInterface $definition
     */
    protected function removeRuleRelations(RuleDefinitionInterface $definition)
    {
        $ruleRelations = $this->ruleRelationRepo->findBy(['rule' => $definition->getId()]);
        $this->ruleRelationRemover->removeAll($ruleRelations);
    }
}
