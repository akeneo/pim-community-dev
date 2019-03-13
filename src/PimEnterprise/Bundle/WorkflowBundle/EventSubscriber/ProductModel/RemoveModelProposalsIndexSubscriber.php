<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\EventSubscriber\ProductModel;

use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\ProductModel\Query\DescendantProductIdsQueryInterface;
use Pim\Component\Catalog\ProductModel\Query\DescendantProductModelIdsQueryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Elasticsearch\Indexer\ProductModelProposalIndexer;
use PimEnterprise\Bundle\WorkflowBundle\Elasticsearch\Indexer\ProductProposalIndexer;
use PimEnterprise\Component\Workflow\Query\SelectModelProposalIdsFromProductModelIdsQueryInterface;
use PimEnterprise\Component\Workflow\Query\SelectProposalIdsFromProductIdsQueryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class RemoveModelProposalsIndexSubscriber implements EventSubscriberInterface
{
    /** @var SelectProposalIdsFromProductIdsQueryInterface  */
    private $selectModelProposalIdsFromProductModelIdsQuery;

    /** @var SelectProposalIdsFromProductIdsQueryInterface */
    private $selectProposalIdsFromProductIdsQuery;

    /** @var DescendantProductIdsQueryInterface */
    private $descendantProductIdsQuery;

    /** @var DescendantProductModelIdsQueryInterface */
    private $descendantProductModelIdsQuery;

    /** @var ProductModelProposalIndexer */
    private $productModelProposalIndexer;

    /** @var ProductProposalIndexer */
    private $productProposalIndexer;

    /** @var int[] */
    private $proposalModelIdsToDelete = [];

    /** @var int[] */
    private $proposalIdsToDelete = [];

    public function __construct(
        SelectModelProposalIdsFromProductModelIdsQueryInterface $selectModelProposalIdsFromProductModelIdsQuery,
        SelectProposalIdsFromProductIdsQueryInterface $selectProposalIdsFromProductIdsQuery,
        DescendantProductIdsQueryInterface $descendantProductIdsQuery,
        DescendantProductModelIdsQueryInterface $descendantProductModelIdsQuery,
        ProductModelProposalIndexer $productModelProposalIndexer,
        ProductProposalIndexer $productProposalIndexer
    ) {
        $this->selectModelProposalIdsFromProductModelIdsQuery = $selectModelProposalIdsFromProductModelIdsQuery;
        $this->selectProposalIdsFromProductIdsQuery = $selectProposalIdsFromProductIdsQuery;
        $this->descendantProductIdsQuery = $descendantProductIdsQuery;
        $this->descendantProductModelIdsQuery = $descendantProductModelIdsQuery;
        $this->productModelProposalIndexer = $productModelProposalIndexer;
        $this->productProposalIndexer = $productProposalIndexer;
    }

    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::PRE_REMOVE => ['calculateImpactedModelProposals', 300],
            StorageEvents::POST_REMOVE => ['removeProductModelProposals', 300],
        ];
    }

    /**
     * @param GenericEvent $event
     */
    public function calculateImpactedModelProposals(GenericEvent $event)
    {
        if (!$event->getSubject() instanceof ProductModelInterface) {
            return;
        }

        $productModelId = $event->getSubject()->getId();
        $productModelIds = $this->descendantProductModelIdsQuery->fetchFromParentProductModelId($productModelId);
        $productModelIds[] = $productModelId;

        $productIds = $this->descendantProductIdsQuery->fetchFromProductModelIds($productModelIds);

        $this->proposalModelIdsToDelete = $this->selectModelProposalIdsFromProductModelIdsQuery->fetch($productModelIds);
        $this->proposalIdsToDelete = $this->selectProposalIdsFromProductIdsQuery->fetch($productIds);
    }

    /**
     * As the model proposals are already deleted from database using cascade delete
     * We "only" need to remove model proposals from ES index
     *
     * @param GenericEvent $event
     */
    public function removeProductModelProposals(GenericEvent $event)
    {
        if (!empty($this->proposalModelIdsToDelete)) {
            $this->productModelProposalIndexer->removeAll($this->proposalModelIdsToDelete);
            $this->proposalModelIdsToDelete = [];
        }

        if (!empty($this->proposalIdsToDelete)) {
            $this->productProposalIndexer->removeAll($this->proposalIdsToDelete);
            $this->proposalIdsToDelete = [];
        }
    }
}
