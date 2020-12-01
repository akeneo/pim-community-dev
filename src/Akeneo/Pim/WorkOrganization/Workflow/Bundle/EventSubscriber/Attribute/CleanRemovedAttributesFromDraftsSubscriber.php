<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\Attribute;

use Akeneo\Pim\Structure\Bundle\Event\AttributeEvents;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\FindDraftIdsConcerningRemovedAttributesQueryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Removes changes on deleted attributes in all products and product models drafts
 */
final class CleanRemovedAttributesFromDraftsSubscriber implements EventSubscriberInterface
{
    /** @var EntityWithValuesDraftRepositoryInterface */
    private $productDraftRepository;

    /** @var EntityWithValuesDraftRepositoryInterface */
    private $productModelDraftRepository;

    /** @var SaverInterface */
    private $productDraftSaver;

    /** @var SaverInterface */
    private $productModelDraftSaver;

    /** @var RemoverInterface */
    private $productDraftRemover;

    /** @var RemoverInterface */
    private $productModelDraftRemover;

    /** @var FindDraftIdsConcerningRemovedAttributesQueryInterface */
    private $findDraftIdsConcerningRemovedAttributesQuery;

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
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            AttributeEvents::POST_CLEAN => 'saveAffectedDrafts',
        ];
    }

    /**
     * Find product and product model drafts containing changes on removed attributes and save them.
     */
    public function saveAffectedDrafts(): void
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
                $this->productDraftSaver->save($productDraft);

                if (!$productDraft->hasChanges()) {
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
                $this->productModelDraftSaver->save($productModelDraft);

                if (!$productModelDraft->hasChanges()) {
                    $this->productModelDraftRemover->remove($productModelDraft);
                }
            }
        }
    }
}
