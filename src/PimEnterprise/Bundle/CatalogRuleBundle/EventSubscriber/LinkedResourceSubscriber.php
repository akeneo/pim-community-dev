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
use Pim\Bundle\CatalogBundle\Event;
use Pim\Bundle\CatalogBundle\Event\AttributeEvents;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleBuilder;
use PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleSelector;
use PimEnterprise\Bundle\CatalogRuleBundle\Manager\RuleLinkedResourceManager;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\RuleLinkedResource;
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

    /** @var ProductRuleSelector */
    protected $productRuleSelector;

    /** @var ProductRuleBuilder */
    protected $productRuleBuilder;

    /** @var string */
    protected $ruleLinkedResClass;

    /**
     * Constructor
     *
     * @param RuleLinkedResourceManager $linkedResManager
     * @param EntityRepository          $ruleLinkedResRepo
     * @param ProductRuleSelector       $productRuleSelector
     * @param ProductRuleBuilder        $productRuleBuilder
     * @param string                    $ruleLinkedResClass
     */
    public function __construct(
        RuleLinkedResourceManager $linkedResManager,
        EntityRepository $ruleLinkedResRepo,
        ProductRuleSelector $productRuleSelector,
        ProductRuleBuilder $productRuleBuilder,
        $ruleLinkedResClass
    ) {
        $this->linkedResManager    = $linkedResManager;
        $this->ruleLinkedResRepo   = $ruleLinkedResRepo;
        $this->productRuleSelector = $productRuleSelector;
        $this->productRuleBuilder  = $productRuleBuilder;
        $this->ruleLinkedResClass  = $ruleLinkedResClass;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            AttributeEvents::PRE_REMOVE => 'deleteRuleLinkedResource',
            RuleEvents::POST_SAVE       => 'saveRuleLinkedResource'
        ];
    }

    /**
     * Deletes a rule linked resource
     *
     * @param GenericEvent $event
     */
    public function deleteRuleLinkedResource(GenericEvent $event)
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
     * Saves a rule linked resource
     *
     * @param RuleEvent $event
     */
    public function saveRuleLinkedResource(RuleEvent $event)
    {
        $definition = $event->getDefinition();

        $rule = $this->productRuleBuilder->build($definition);

        $actions = $rule->getActions();

        $impactedAttributes = $this->linkedResManager->getImpactedAttributes($actions);
        $this->executeSave($definition, $impactedAttributes);
    }

    /**
     * Instanciate a new rule linked resource
     *
     * @param RuleDefinitionInterface $rule
     * @param AttributeInterface      $attribute
     *
     * @return RuleLinkedResource
     */
    protected function instanciate(RuleDefinitionInterface $rule, AttributeInterface $attribute)
    {
        /** @var RuleLinkedResource $ruleLinkedResource */
        $ruleLinkedResource = new $this->ruleLinkedResClass();
        $ruleLinkedResource->setRule($rule);
        $ruleLinkedResource->setResourceName(ClassUtils::getClass($attribute));
        $ruleLinkedResource->setResourceId($attribute->getId());

        return $ruleLinkedResource;
    }

    /**
     * Save fetched objects
     *
     * @param RuleDefinitionInterface $rule
     * @param array                   $impactedAttributes
     */
    protected function executeSave(RuleDefinitionInterface $rule, array $impactedAttributes)
    {
        foreach ($impactedAttributes as $impactedAttribute) {
            $ruleLinkedResource = $this->ruleLinkedResRepo->find($rule);
            if (isset($ruleLinkedResource)) {
                $this->linkedResManager->remove($ruleLinkedResource);
            }

            $ruleLinkedResource = $this->instanciate($rule, $impactedAttribute);
            $this->linkedResManager->save($ruleLinkedResource);
        }
    }
}
