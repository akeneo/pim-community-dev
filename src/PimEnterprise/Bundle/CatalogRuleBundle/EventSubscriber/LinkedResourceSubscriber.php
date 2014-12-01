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

use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
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
     * @param AttributeRepository       $attributeRepository
     */
    public function __construct(
        RuleLinkedResourceManager $linkedResManager,
        EntityRepository $ruleLinkedResRepo,
        ProductRuleSelector $productRuleSelector,
        ProductRuleLoader $productRuleLoader,
        AttributeRepository $attributeRepository
    ) {
        $this->linkedResManager    = $linkedResManager;
        $this->ruleLinkedResRepo   = $ruleLinkedResRepo;
        $this->productRuleSelector = $productRuleSelector;
        $this->productRuleLoader   = $productRuleLoader;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            AttributeEvents::PRE_REMOVE  => 'deleteRuleLinkedResource',
            RuleEvents::POST_SAVE        => 'saveRuleLinkedResource',
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

        $this->executeSave($actions, $rule);
    }

    /**
     * Execute the save of the rule linked resource
     *
     * @param $actions
     * @param $rule
     */
    protected function executeSave($actions, $rule)
    {
        $fields = [];
        foreach ($actions as $action) {
            if (array_key_exists('field', $action)) {
                $fields[] = $action['field'];
            }
            if (array_key_exists('to_field', $action)) {
                $fields[] = $action['to_field'];
            }
        }

        $impactedAttributes = [];
        foreach ($fields as $field) {
            $impactedAttributes[] = $this->attributeRepository->findByReference($field);
        }

        $impactedAttributes = array_unique($impactedAttributes, SORT_STRING);

        foreach ($impactedAttributes as $impactedAttribute) {
            $ruleLinkedResource = $this->instanciate($rule, $impactedAttribute);
            $this->linkedResManager->save($ruleLinkedResource);
        }
    }

    /**
     * Instanciate a new rule linked resource
     *
     * @param $rule
     * @param $attribute
     *
     * @return RuleLinkedResource
     */
    protected function instanciate($rule, $attribute)
    {
        $ruleLinkedResource = new RuleLinkedResource();
        $ruleLinkedResource->setRule($rule);
        $ruleLinkedResource->setResourceName(ClassUtils::getClass($attribute));
        $ruleLinkedResource->setResourceId($attribute->getId());

        return $ruleLinkedResource;
    }
}
