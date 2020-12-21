<?php

declare(strict_types=1);

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Cleaner;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\FindDraftIdsConcerningRemovedAttributesQueryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;

/**
 * Removes changes on deleted attributes in all products and product models drafts
 */
final class RemovedAttributeCleaner
{
    private EntityWithValuesDraftRepositoryInterface $productDraftRepository;
    private EntityWithValuesDraftRepositoryInterface $productModelDraftRepository;
    private SaverInterface $productDraftSaver;
    private SaverInterface $productModelDraftSaver;
    private RemoverInterface $productDraftRemover;
    private RemoverInterface $productModelDraftRemover;
    private FindDraftIdsConcerningRemovedAttributesQueryInterface $findDraftIdsConcerningRemovedAttributesQuery;

    public function __construct(
        EntityWithValuesDraftRepositoryInterface $productDraftRepository,
        EntityWithValuesDraftRepositoryInterface $productModelDraftRepository,
        SaverInterface $productDraftSaver,
        SaverInterface $productModelDraftSaver,
        RemoverInterface $productDraftRemover,
        RemoverInterface $productModelDraftRemover,
        FindDraftIdsConcerningRemovedAttributesQueryInterface $findDraftIdsConcerningRemovedAttributesQuery
    ) {
        $this->productDraftRepository = $productDraftRepository;
        $this->productModelDraftRepository = $productModelDraftRepository;
        $this->productDraftSaver = $productDraftSaver;
        $this->productModelDraftSaver = $productModelDraftSaver;
        $this->productDraftRemover = $productDraftRemover;
        $this->productModelDraftRemover = $productModelDraftRemover;
        $this->findDraftIdsConcerningRemovedAttributesQuery = $findDraftIdsConcerningRemovedAttributesQuery;
    }

    /**
     * Find product and product model drafts containing changes on removed attributes and save them.
     */
    public function cleanAffectedDrafts(): void
    {
        $productDraftIds = $this->findDraftIdsConcerningRemovedAttributesQuery->forProducts();
        $productModelDraftIds = $this->findDraftIdsConcerningRemovedAttributesQuery->forProductModels();

        $this->saveProductDrafts($productDraftIds);
        $this->saveProductModelDrafts($productModelDraftIds);
    }

    private function saveProductDrafts(\Iterator $productDraftIds): void
    {
        foreach ($productDraftIds as $productDraftIdBatch) {
            $productDrafts = $this->productDraftRepository->findByIds($productDraftIdBatch);

            if (null === $productDrafts) {
                return;
            }

            foreach ($productDrafts as $productDraft) {
                if ($productDraft->hasChanges()) {
                    $this->productDraftSaver->save($productDraft);
                } else {
                    $this->productDraftRemover->remove($productDraft);
                }
            }
        }
    }

    private function saveProductModelDrafts(\Iterator $productModelDraftIds): void
    {
        foreach ($productModelDraftIds as $productModelDraftIdBatch) {
            $productModelDrafts = $this->productModelDraftRepository->findByIds($productModelDraftIdBatch);

            if (null === $productModelDrafts) {
                return;
            }

            foreach ($productModelDrafts as $productModelDraft) {
                if ($productModelDraft->hasChanges()) {
                    $this->productModelDraftSaver->save($productModelDraft);
                } else {
                    $this->productModelDraftRemover->remove($productModelDraft);
                }
            }
        }
    }
}
