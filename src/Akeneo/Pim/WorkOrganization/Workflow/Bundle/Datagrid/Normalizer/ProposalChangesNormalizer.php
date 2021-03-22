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
use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Helper\ProductDraftChangesPermissionHelper;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Twig\ProductDraftChangesExtension;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Twig\ProductDraftStatusGridExtension;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProposalChangesNormalizer
{
    private NormalizerInterface $standardNormalizer;
    private ValueFactory $valueFactory;
    private ProductDraftChangesExtension $changesExtension;
    private AuthorizationCheckerInterface $authorizationChecker;
    private AttributeRepositoryInterface $attributeRepository;
    private LocaleRepositoryInterface $localeRepository;
    private ProductDraftStatusGridExtension $statusExtension;
    private ProductDraftChangesPermissionHelper $permissionHelper;

    public function __construct(
        NormalizerInterface $standardNormalizer,
        ValueFactory $valueFactory,
        ProductDraftChangesExtension $changesExtension,
        AuthorizationCheckerInterface $authorizationChecker,
        AttributeRepositoryInterface $attributeRepository,
        LocaleRepositoryInterface $localeRepository,
        ProductDraftStatusGridExtension $statusExtension,
        ProductDraftChangesPermissionHelper $permissionHelper
    ) {
        $this->standardNormalizer = $standardNormalizer;
        $this->valueFactory = $valueFactory;
        $this->changesExtension = $changesExtension;
        $this->authorizationChecker = $authorizationChecker;
        $this->attributeRepository = $attributeRepository;
        $this->localeRepository = $localeRepository;
        $this->statusExtension = $statusExtension;
        $this->permissionHelper = $permissionHelper;
    }

    public function normalize(EntityWithValuesDraftInterface $proposal, array $context = []): array
    {
        $canReview = $this->permissionHelper->canEditOneChangeToReview($proposal);
        $canDelete = $this->permissionHelper->canEditOneChangeDraft($proposal);
        $toReview = $proposal->getStatus() === EntityWithValuesDraftInterface::READY;
        $inProgress = $proposal->isInProgress();
        $isOwner = $this->authorizationChecker->isGranted(Attributes::OWN, $proposal->getEntityWithValue());

        $result = [
            'status_label' => $this->statusExtension->getDraftStatusGrid($proposal),
        ];

        $changesWithEmptyValues = $this->getChanges($proposal, $context);
        if ($proposal->getStatus() === EntityWithValuesDraftInterface::IN_PROGRESS) {
            return array_merge($result, [
                'status' => 'in_progress',
                'remove'  => $inProgress && $isOwner && $canDelete
            ]);
        }
        $proposalChanges = [];
        foreach ($changesWithEmptyValues as $attributeCode => $changes) {
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
            $canView = $this->authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $attribute);
            if ($canView) {
                $proposalChanges[$attributeCode] = [];
                foreach ($changes as $change) {
                    $locale = $this->localeRepository->findOneByIdentifier($change['locale']);
                    $canViewLocale = !$attribute->isLocalizable() || $this->authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $locale);
                    if ($canViewLocale) {
                        $canReview =
                            $this->authorizationChecker->isGranted(Attributes::EDIT_ATTRIBUTES, $attribute) &&
                            $this->authorizationChecker->isGranted(Attributes::OWN, $proposal->getEntityWithValue()) &&
                            (!$attribute->isLocalizable() || $this->authorizationChecker->isGranted(Attributes::EDIT_ITEMS, $locale));
                        /** @var array $present */
                        $present = $this->changesExtension->presentChange($proposal, $change, $attributeCode);
                        if (count($present) > 0) {
                            $present['attributeLabel'] = $attribute->getLabel();
                            $present['scope'] = $change['scope'];
                            $present['locale'] = $change['locale'];
                            $present['canReview'] = $canReview;
                            $proposalChanges[$attributeCode][] = $present;
                        }
                    }
                }
            }
        }

        return array_merge($result, [
            'status' => 'ready',
            'search_id' => $proposal->getEntityWithValue()->getIdentifier(),
            'changes' => $proposalChanges,
            'author_code' => $proposal->getAuthor(),
            'approve' => $isOwner && $toReview && $canReview,
            'refuse'  => $isOwner && $toReview && $canReview,
            'id' => $proposal->getId(),
        ]);
    }

    private function getChanges(EntityWithValuesDraftInterface $proposal, array $context): array
    {
        $normalizedValues = $this->standardNormalizer->normalize(
            $this->getValueCollectionFromChangesWithoutEmptyValues($proposal),
            'standard',
            $context
        );

        $changes = $proposal->getChanges();
        foreach ($changes['values'] as $code => $changeset) {
            foreach ($changeset as $index => $change) {
                if ($this->isChangeDataNull($change['data'])) {
                    $normalizedValues[$code][] = [
                        'data' => null,
                        'locale' => $change['locale'],
                        'scope' => $change['scope']
                    ];
                }
            }
        }

        return $normalizedValues;
    }

    /**
     * During the fetch of the Draft, the ValueCollectionFactory will remove the empty values. As empty values are
     * filtered in the raw values, deleted values are not rendered properly for the proposal.
     * As the ValueCollectionFactory is used for the Draft too, the $rawValues does not contains empty values anymore.
     * This implies that the proposal are not correctly displayed in the datagrid if you use the $rawValues.
     * So, instead of using the $rawValues, we recalculate the values to display from the $changes field.
     *
     * https://github.com/akeneo/pim-community-dev/issues/10083
     */
    private function getValueCollectionFromChangesWithoutEmptyValues(EntityWithValuesDraftInterface $proposal): WriteValueCollection
    {
        $changes = $proposal->getChanges();
        $valueCollection = new WriteValueCollection();

        foreach ($changes['values'] as $code => $changeset) {
            $attribute = $this->attributeRepository->findOneByIdentifier($code);
            foreach ($changeset as $index => $change) {
                if (true === $this->isChangeDataNull($change['data'])) {
                    continue;
                }

                if (false === $this->changeNeedsReview($proposal, $code, $change['locale'], $change['scope'])) {
                    continue;
                }

                $valueCollection->add($this->valueFactory->createByCheckingData(
                    $attribute,
                    $change['scope'],
                    $change['locale'],
                    $change['data']
                ));
            }
        }

        return $valueCollection;
    }

    private function isChangeDataNull($changeData): bool
    {
        return null === $changeData || '' === $changeData || [] === $changeData;
    }

    private function changeNeedsReview(
        EntityWithValuesDraftInterface $proposal,
        string $code,
        ?string $localeCode,
        ?string $channelCode
    ): bool {
        return EntityWithValuesDraftInterface::CHANGE_TO_REVIEW === $proposal->getReviewStatusForChange($code, $localeCode, $channelCode);
    }
}
