<?php
/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\EventSubscriber;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityRepository;
use JMS\DiExtraBundle\Generator\DefinitionInjectorGenerator;
use Pim\Bundle\CatalogBundle\Event;
use Pim\Bundle\CatalogBundle\Event\AttributeEvents;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleBuilder;
use PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleSelector;
use PimEnterprise\Bundle\CatalogRuleBundle\Manager\RuleLinkedResourceManager;
use PimEnterprise\Bundle\RuleEngineBundle\Event\BulkRuleEvent;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvent;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvents;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Linked resource subscriber
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class LinkedResourceSubscriber implements EventSubscriberInterface
{
    /** @var RuleLinkedResourceManager */
    protected $linkedResManager;

    /** @var EntityRepository */
    protected $ruleLinkedResRepo;

    /** @var ProductRuleBuilder */
    protected $productRuleBuilder;

    /** @var string */
    protected $ruleLinkedResClass;

    /**
     * Constructor
     *
     * @param RuleLinkedResourceManager $linkedResManager
     * @param EntityRepository          $ruleLinkedResRepo
     * @param ProductRuleBuilder        $productRuleBuilder
     * @param string                    $ruleLinkedResClass
     */
    public function __construct(
        RuleLinkedResourceManager $linkedResManager,
        EntityRepository $ruleLinkedResRepo,
        ProductRuleBuilder $productRuleBuilder,
        $ruleLinkedResClass
    ) {
        $this->linkedResManager    = $linkedResManager;
        $this->ruleLinkedResRepo   = $ruleLinkedResRepo;
        $this->productRuleBuilder  = $productRuleBuilder;
        $this->ruleLinkedResClass  = $ruleLinkedResClass;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            AttributeEvents::PRE_REMOVE => 'removeAttribute',
            RuleEvents::POST_SAVE       => 'saveRule',
            RuleEvents::POST_SAVE_ALL   => 'saveRules'
        ];
    }

    /**
     * Deletes a rule linked resource
     *
     * @param GenericEvent $event
     */
    public function removeAttribute(GenericEvent $event)
    {
        $entity = $event->getSubject();
        $ruleLinkedResources = [];

        if ($entity instanceof AttributeInterface) {
            $ruleLinkedResources = $this->ruleLinkedResRepo
                ->findBy(['resourceId' => $entity->getId(), 'resourceName' => ClassUtils::getClass($entity)]);
        }

        foreach ($ruleLinkedResources as $ruleLinkedResource) {
            $this->linkedResManager->remove($ruleLinkedResource);
        }
    }

    /**
     * When saves a single rule
     *
     * @param RuleEvent $event
     */
    public function saveRule(RuleEvent $event)
    {
        $definition = $event->getDefinition();
        $this->saveRuleLinkedResources($definition);
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
            $this->saveRuleLinkedResources($definition);
        }
    }

    /**
     * Saves a rule linked resource
     *
     * @param RuleDefinitionInterface $definition
     */
    protected function saveRuleLinkedResources(RuleDefinitionInterface $definition)
    {
        if (null === $definition->getId()) {
            return;
        }

        $this->removeRuleLinkedResources($definition);
        $this->addRuleLinkedResources($definition);
    }

    /**
     * @param RuleDefinitionInterface $definition
     */
    protected function addRuleLinkedResources(RuleDefinitionInterface $definition)
    {
        $rule = $this->productRuleBuilder->build($definition);
        $actions = $rule->getActions();
        $linkedAttributes = $this->linkedResManager->getImpactedAttributes($actions);

        foreach ($linkedAttributes as $linkedAttribute) {
            $ruleLinkedResource = new $this->ruleLinkedResClass();
            $ruleLinkedResource->setRule($definition);
            $ruleLinkedResource->setResourceName(ClassUtils::getClass($linkedAttribute));
            $ruleLinkedResource->setResourceId($linkedAttribute->getId());

            $this->linkedResManager->save($ruleLinkedResource);
        }
    }

    /**
     * @param RuleDefinitionInterface $definition
     */
    protected function removeRuleLinkedResources(RuleDefinitionInterface $definition)
    {
        $ruleLinkedResources = $this->ruleLinkedResRepo->findBy(['rule' => $definition->getId()]);
        foreach ($ruleLinkedResources as $resource) {
            $this->linkedResManager->remove($resource);
        }
    }
}
