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

use Pim\Bundle\CatalogBundle\Event;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\Event\AttributeEvents;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleLoader;
use PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleSelector;
use PimEnterprise\Bundle\CatalogRuleBundle\Manager\RuleLinkedResourceManager;
use Doctrine\ORM\EntityRepository;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\RuleLinkedResource;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvent;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvents;
use PimEnterprise\Bundle\RuleEngineBundle\Model\Rule;
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

    /** @var ProductRuleLoader */
    protected $productRuleLoader;

    /** @var string */
    protected $ruleLinkedReClass;

    /**
     * Constructor
     *
     * @param RuleLinkedResourceManager $linkedResManager
     * @param EntityRepository          $ruleLinkedResRepo
     * @param ProductRuleSelector       $productRuleSelector
     * @param ProductRuleLoader         $productRuleLoader
     * @param string                    $ruleLinkedReClass
     */
    public function __construct(
        RuleLinkedResourceManager $linkedResManager,
        EntityRepository $ruleLinkedResRepo,
        ProductRuleSelector $productRuleSelector,
        ProductRuleLoader $productRuleLoader,
        $ruleLinkedReClass
    ) {
        $this->linkedResManager    = $linkedResManager;
        $this->ruleLinkedResRepo   = $ruleLinkedResRepo;
        $this->productRuleSelector = $productRuleSelector;
        $this->productRuleLoader   = $productRuleLoader;
        $this->ruleLinkedReClass   = $ruleLinkedReClass;
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
        $rule = $event->getRule();

        $loadedRule = $this->productRuleLoader->load($rule);

        $actions = $loadedRule->getActions();

        $impactedAttributes = $this->linkedResManager->getImpactedAttributes($actions);
        $this->executeSave($rule, $impactedAttributes);
    }

    /**
     * Instanciate a new rule linked resource
     *
     * @param Rule               $rule
     * @param AttributeInterface $attribute
     *
     * @return RuleLinkedResource
     */
    protected function instanciate(Rule $rule, AttributeInterface $attribute)
    {
        /** @var RuleLinkedResource $ruleLinkedResource */
        $ruleLinkedResource = new $this->ruleLinkedReClass();
        $ruleLinkedResource->setRule($rule);
        $ruleLinkedResource->setResourceName(ClassUtils::getClass($attribute));
        $ruleLinkedResource->setResourceId($attribute->getId());

        return $ruleLinkedResource;
    }

    /**
     * Save fetched objects
     *
     * @param Rule  $rule
     * @param array $impactedAttributes
     */
    protected function executeSave(Rule $rule, array $impactedAttributes)
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
