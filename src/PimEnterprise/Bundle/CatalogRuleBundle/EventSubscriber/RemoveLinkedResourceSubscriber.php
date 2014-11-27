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
use PimEnterprise\Bundle\CatalogRuleBundle\Manager\RuleLinkedResourceManager;
use PimEnterprise\Bundle\CatalogRuleBundle\Repository\RuleLinkedResourceRepositoryInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Attribute Subscriber
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class RemoveLinkedResourceSubscriber implements EventSubscriberInterface
{
    /** @var RuleLinkedResourceManager */
    protected $linkedResManager;

    /** @var RuleLinkedResourceRepositoryInterface */
    protected $ruleLinkedResRepo;

    /**
     * Constructor
     *
     * @param RuleLinkedResourceManager             $linkedResManager
     * @param RuleLinkedResourceRepositoryInterface $ruleLinkedResRepo
     */
    public function __construct(
        RuleLinkedResourceManager             $linkedResManager,
        RuleLinkedResourceRepositoryInterface $ruleLinkedResRepo
    ) {
        $this->linkedResManager  = $linkedResManager;
        $this->ruleLinkedResRepo = $ruleLinkedResRepo;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            Event\AttributeEvents::PRE_REMOVE => 'deleteRuleLinkedResource',
        ];
    }

    /**
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
}
