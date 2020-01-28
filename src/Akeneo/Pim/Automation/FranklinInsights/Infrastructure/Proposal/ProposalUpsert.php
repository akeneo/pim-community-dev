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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Proposal;

use Akeneo\Pim\Automation\FranklinInsights\Application\Proposal\Service\ProposalUpsertInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Proposal\Factory\FranklinUserDraftSourceFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Builder\EntityWithValuesDraftBuilderInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Event\EntityWithValuesDraftEvents;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\DraftSource;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
final class ProposalUpsert implements ProposalUpsertInterface
{
    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var ObjectUpdaterInterface */
    private $productUpdater;

    /** @var EntityWithValuesDraftBuilderInterface */
    private $draftBuilder;

    /** @var SaverInterface */
    private $draftSaver;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var FranklinUserDraftSourceFactory */
    private $draftSourceFactory;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param ObjectUpdaterInterface $productUpdater
     * @param EntityWithValuesDraftBuilderInterface $draftBuilder
     * @param SaverInterface $draftSaver
     * @param EventDispatcherInterface $eventDispatcher
     * @param FranklinUserDraftSourceFactory $draftSourceFactory
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ObjectUpdaterInterface $productUpdater,
        EntityWithValuesDraftBuilderInterface $draftBuilder,
        SaverInterface $draftSaver,
        EventDispatcherInterface $eventDispatcher,
        FranklinUserDraftSourceFactory $draftSourceFactory
    ) {
        $this->productRepository = $productRepository;
        $this->productUpdater = $productUpdater;
        $this->draftBuilder = $draftBuilder;
        $this->draftSaver = $draftSaver;
        $this->eventDispatcher = $eventDispatcher;
        $this->draftSourceFactory = $draftSourceFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function process(array $suggestedData): int
    {
        $processed = 0;
        foreach ($suggestedData as $data) {
            $product = $this->productRepository->find($data->getproductId()->toInt());
            if (null === $product) {
                continue;
            }

            try {
                $wasProposalCreated = $this->doProcess(
                    $product,
                    $this->filterAttributesNotBelongingToTheFamily($product->getFamily(), $data->getSuggestedValues()),
                    $this->draftSourceFactory->create()
                );
                if (true === $wasProposalCreated) {
                    $processed++;
                }
            } catch (\LogicException $e) {
                continue;
            }
        }

        return $processed;
    }

    /**
     * @param ProductInterface $product
     * @param array $values
     * @param DraftSource $draftSource
     * @return bool
     */
    private function doProcess(ProductInterface $product, array $values, DraftSource $draftSource): bool
    {
        $this->productUpdater->update(
            $product,
            [
                'values' => $values,
            ]
        );
        $productDraft = $this->draftBuilder->build($product, $draftSource);

        if (null !== $productDraft) {
            $this->eventDispatcher->dispatch(
                EntityWithValuesDraftEvents::PRE_READY,
                new GenericEvent($productDraft)
            );

            $productDraft->setAllReviewStatuses(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW);
            $productDraft->markAsReady();
            $this->draftSaver->save($productDraft);

            $this->eventDispatcher->dispatch(
                EntityWithValuesDraftEvents::POST_READY,
                new GenericEvent($productDraft, ['comment' => null])
            );

            return true;
        }

        return false;
    }

    /**
     * Removes values for attributes that do not belong to the provided family.
     *
     * @param FamilyInterface $family
     * @param array $values
     *
     * @return array
     */
    private function filterAttributesNotBelongingToTheFamily(FamilyInterface $family, array $values): array
    {
        $familyAttributeCodes = $family->getAttributeCodes();
        foreach ($values as $attributeCode => $data) {
            if (!in_array($attributeCode, $familyAttributeCodes)) {
                unset($values[$attributeCode]);
            }
        }

        return $values;
    }
}
