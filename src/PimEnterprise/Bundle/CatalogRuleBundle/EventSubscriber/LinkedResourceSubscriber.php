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
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleLoader;
use PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleSelector;
use PimEnterprise\Bundle\CatalogRuleBundle\Manager\RuleLinkedResourceManager;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\RuleLinkedResource;
use Doctrine\ORM\EntityRepository;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvent;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvents;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
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
    private $productRuleLoader;

    /**
     * Constructor
     *
     * @param RuleLinkedResourceManager $linkedResManager
     * @param EntityRepository          $ruleLinkedResRepo
     * @param ProductRuleSelector       $productRuleSelector
     * @param ProductRuleLoader         $productRuleLoader
     */
    public function __construct(
        RuleLinkedResourceManager $linkedResManager,
        EntityRepository $ruleLinkedResRepo,
        ProductRuleSelector $productRuleSelector,
        ProductRuleLoader $productRuleLoader
    ) {
        $this->linkedResManager    = $linkedResManager;
        $this->ruleLinkedResRepo   = $ruleLinkedResRepo;
        $this->productRuleSelector = $productRuleSelector;
        $this->productRuleLoader   = $productRuleLoader;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Event\AttributeEvents::PRE_REMOVE => 'deleteRuleLinkedResource',
            RuleEvents::POST_SAVE             => 'saveRuleLinkedResource',
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
        if ($entity instanceof RuleInterface) {
            $ruleLinkedResources = $this->ruleLinkedResRepo
                ->findBy(['rule' => $entity]);
        }

        foreach ($ruleLinkedResources as $ruleLinkedResource) {
            $this->linkedResManager->remove($ruleLinkedResource);
        }
    }

    /**
     * Saves a rule linked resource
     *
     * @param RuleEvent|GenericEvent $event
     */
    public function saveRuleLinkedResource(RuleEvent $event)
    {
        $entity = $event->getRule();

        $loadedRule = $this->productRuleLoader->load($entity);

        $subjects = $this->productRuleSelector->select($loadedRule);

        $actions = $loadedRule->getActions();

        $this->executeSave($actions, $subjects, $entity);

    }

    /**
     * Execute the save of the rule linked resource
     *
     * todo: move this function in a repo and remove the O(N2) complexity
     *
     * @param $actions
     * @param $subjects
     * @param $entity
     */
    protected function executeSave($actions, $subjects, $entity)
    {
        $setField = $actions[0]['field'];
        $copyField = $actions[1]['to_field'];

        $products = $subjects->getSubjects();

        foreach ($products as $product) {
            foreach ($product->getValues() as $productValue) {
                if ($productValue->getAttribute()->getCode() === $setField
                    || $productValue->getAttribute()->getCode() === $copyField
                ) {
                    var_dump($productValue->getAttribute()->getCode());
                    $ruleLinkedResource = new RuleLinkedResource();
                    $ruleLinkedResource->setRule($entity);
                    $ruleLinkedResource->setResourceName(ClassUtils::getClass($productValue->getAttribute()));
                    $ruleLinkedResource->setResourceId($productValue->getAttribute()->getId());
                    $this->linkedResManager->save($ruleLinkedResource);
                }
            }
        }
    }
}
