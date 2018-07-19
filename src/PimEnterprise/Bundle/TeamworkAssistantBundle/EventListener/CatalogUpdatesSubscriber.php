<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\TeamworkAssistantBundle\EventListener;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PimEnterprise\Bundle\TeamworkAssistantBundle\Job\RefreshProjectCompletenessJobLauncher;
use Akeneo\Pim\WorkOrganization\TeamWorkAssistant\Component\Model\ProjectInterface;
use Akeneo\Pim\WorkOrganization\TeamWorkAssistant\Component\Remover\ChainedProjectRemover;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * The goal of this subscriber is to listen on catalog updates events to be able to know if entities updates/removing
 * has impact on projects. If it's the case, it removes relevant projects.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class CatalogUpdatesSubscriber implements EventSubscriberInterface
{
    /** @var ChainedProjectRemover */
    protected $chainedProjectRemover;

    /** @var RequestStack */
    protected $requestStack;

    /** @var RefreshProjectCompletenessJobLauncher */
    protected $jobLauncher;

    /**
     * @param ChainedProjectRemover                 $chainedProjectRemover
     * @param RefreshProjectCompletenessJobLauncher $jobLauncher
     * @param RequestStack                          $requestStack
     */
    public function __construct(
        ChainedProjectRemover $chainedProjectRemover,
        RefreshProjectCompletenessJobLauncher $jobLauncher,
        RequestStack $requestStack
    ) {
        $this->chainedProjectRemover = $chainedProjectRemover;
        $this->requestStack = $requestStack;
        $this->jobLauncher = $jobLauncher;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_REMOVE => 'removeProjectsImpactedByEntity',
            StorageEvents::POST_SAVE  => [
                ['removeProjectsImpactedByEntity', 100],
                ['updatePreProcessedData', 50]
            ]
        ];
    }

    /**
     * Update the attribute group completeness when the product is updated from the UI
     *
     * @param GenericEvent $event
     */
    public function updatePreProcessedData(GenericEvent $event)
    {
        if (null === $request = $this->requestStack->getMasterRequest()) {
            return;
        }

        if ('pim_enrich_product_rest_post' !== $request->attributes->get('_route')) {
            return;
        }

        $product = $event->getSubject();
        if (!$product instanceof ProductInterface) {
            return;
        }

        $this->jobLauncher->launch(
            $product,
            $product->getScope(),
            $product->getLocale()
        );
    }

    /**
     * @param GenericEvent $event
     * @param string       $eventName
     */
    public function removeProjectsImpactedByEntity(GenericEvent $event, $eventName)
    {
        $entity = $event->getSubject();
        if ($entity instanceof ProjectInterface) {
            return;
        }
        $this->chainedProjectRemover->removeProjectsImpactedBy($entity, $eventName);
    }
}
