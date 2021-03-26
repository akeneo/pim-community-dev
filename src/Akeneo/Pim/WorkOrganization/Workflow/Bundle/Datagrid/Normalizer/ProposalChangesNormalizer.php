<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Normalizer;

use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Helper\ProductDraftChangesPermissionHelper;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PresenterRegistry;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProposalChangesNormalizer
{
    private PresenterRegistry $changesExtension;
    private AuthorizationCheckerInterface $authorizationChecker;
    private AttributeRepositoryInterface $attributeRepository;
    private LocaleRepositoryInterface $localeRepository;
    private ProductDraftChangesPermissionHelper $permissionHelper;
    private ValueCollectionWithoutEmptyValuesProvider $valueCollectionWithoutEmptyValuesProvider;

    public function __construct(
        PresenterRegistry $changesExtension,
        AuthorizationCheckerInterface $authorizationChecker,
        AttributeRepositoryInterface $attributeRepository,
        LocaleRepositoryInterface $localeRepository,
        ProductDraftChangesPermissionHelper $permissionHelper,
        ValueCollectionWithoutEmptyValuesProvider $valueCollectionWithoutEmptyValuesProvider
    ) {
        $this->changesExtension = $changesExtension;
        $this->authorizationChecker = $authorizationChecker;
        $this->attributeRepository = $attributeRepository;
        $this->localeRepository = $localeRepository;
        $this->permissionHelper = $permissionHelper;
        $this->valueCollectionWithoutEmptyValuesProvider = $valueCollectionWithoutEmptyValuesProvider;
    }

    public function normalize(EntityWithValuesDraftInterface $entityWithValuesDraft, array $context = []): array
    {
        $canReviewAll = $this->permissionHelper->canEditOneChangeToReview($entityWithValuesDraft);
        $canDelete = $this->permissionHelper->canEditOneChangeDraft($entityWithValuesDraft);
        $inProgress = $entityWithValuesDraft->isInProgress();
        $isOwner = $this->authorizationChecker->isGranted(Attributes::OWN, $entityWithValuesDraft->getEntityWithValue());

        $result = [
            'status_label' => $this->getDraftStatusGrid($entityWithValuesDraft),
        ];

        if ($inProgress) {
            return array_merge($result, [
                'status' => 'in_progress',
                'remove'  => $isOwner && $canDelete
            ]);
        }

        $proposalChanges = [];
        $changesWithEmptyValues = $this->valueCollectionWithoutEmptyValuesProvider->getChanges($entityWithValuesDraft, $context);
        foreach ($changesWithEmptyValues as $attributeCode => $changes) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
            $canView = $this->authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $attribute);
            if (!$canView) {
                continue;
            }

            $proposalChanges[$attributeCode] = [];
            foreach ($changes as $change) {
                if (!$this->canViewLocale($attribute, $change['locale'])) {
                    continue;
                }

                $canReview =
                    $this->authorizationChecker->isGranted(Attributes::EDIT_ATTRIBUTES, $attribute) &&
                    $this->authorizationChecker->isGranted(Attributes::OWN, $entityWithValuesDraft->getEntityWithValue()) &&
                    $this->canEditLocale($attribute, $change['locale']);

                /** @var array $present */
                $present = $this->changesExtension->presentChange($entityWithValuesDraft, $change, $attributeCode);
                if (count($present) > 0) {
                    $present['data'] = $change['data'];
                    $present['attributeLabel'] = $attribute->getLabel();
                    $present['scope'] = $change['scope'];
                    $present['locale'] = $change['locale'];
                    $present['canReview'] = $canReview;
                    $proposalChanges[$attributeCode][] = $present;
                }
            }
        }

        return array_merge($result, [
            'status' => 'ready',
            'search_id' => $entityWithValuesDraft->getEntityWithValue()->getIdentifier(),
            'changes' => $proposalChanges,
            'author_code' => $entityWithValuesDraft->getAuthor(),
            'approve' => $isOwner && $canReviewAll,
            'refuse'  => $isOwner && $canReviewAll,
            'id' => $entityWithValuesDraft->getId(),
        ]);
    }

    private function getDraftStatusGrid(EntityWithValuesDraftInterface $productDraft): string
    {
        $toReview = !$productDraft->isInProgress();
        $canReview = $this->permissionHelper->canEditOneChangeToReview($productDraft);
        $canDelete = $this->permissionHelper->canEditOneChangeDraft($productDraft);
        $canReviewAll = $this->permissionHelper->canEditAllChangesToReview($productDraft);

        if ($toReview) {
            if ($canReviewAll) {
                return 'ready';
            }

            if ($canReview) {
                return 'can_be_partially_reviewed';
            }

            return 'can_not_be_approved';
        }

        if ($canDelete) {
            return 'in_progress';
        }

        return 'can_not_be_deleted';
    }

    private function canViewLocale(AttributeInterface $attribute, ?string $locale): bool
    {
        if (!$attribute->isLocalizable() || null === $locale) {
            return true;
        }

        return $this->authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $this->localeRepository->findOneByIdentifier($locale));
    }

    private function canEditLocale(AttributeInterface $attribute, ?string $locale): bool
    {
        if (!$attribute->isLocalizable() || null === $locale) {
            return true;
        }

        return $this->authorizationChecker->isGranted(Attributes::EDIT_ITEMS, $this->localeRepository->findOneByIdentifier($locale));
    }
}
