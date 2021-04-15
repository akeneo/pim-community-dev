<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Normalizer;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Normalizer\ValueCollectionWithoutEmptyValuesProvider;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Helper\ProductDraftChangesPermissionHelper;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Presenter\PresenterRegistry;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProposalChangesNormalizerSpec extends ObjectBehavior
{
    function let(
        PresenterRegistry $presenterRegistry,
        AuthorizationCheckerInterface $authorizationChecker,
        AttributeRepositoryInterface $attributeRepository,
        LocaleRepositoryInterface $localeRepository,
        ProductDraftChangesPermissionHelper $permissionHelper,
        ValueCollectionWithoutEmptyValuesProvider $valueCollectionWithoutEmptyValuesProvider
    ) {
        $this->beConstructedWith(
            $presenterRegistry,
            $authorizationChecker,
            $attributeRepository,
            $localeRepository,
            $permissionHelper,
            $valueCollectionWithoutEmptyValuesProvider
        );
    }

    function it_normalizes_in_progress_draft(
        AuthorizationCheckerInterface $authorizationChecker,
        ProductDraftChangesPermissionHelper $permissionHelper,
        EntityWithValuesDraftInterface $entityWithValuesDraft,
        EntityWithValuesInterface $entityWithValues
    ) {
        $context = ['locales' => ['en_US']];
        $entityWithValuesDraft->isInProgress()->willReturn(true);
        $entityWithValuesDraft->getEntityWithValue()->willReturn($entityWithValues);
        $entityWithValuesDraft->getChanges()->willReturn(['values' => []]);

        $authorizationChecker->isGranted('OWN_RESOURCE', $entityWithValues)->willReturn(true);
        $permissionHelper->canEditOneChangeToReview($entityWithValuesDraft)->willReturn(true);
        $permissionHelper->canEditOneChangeDraft($entityWithValuesDraft)->willReturn(true);
        $permissionHelper->canEditAllChangesToReview($entityWithValuesDraft)->willReturn(true);

        $this->normalize($entityWithValuesDraft, $context)->shouldReturn([
            'status_label' => 'in_progress',
            'status' => 'in_progress',
            'remove' => true,
        ]);
    }

    function it_normalizes_ready_draft(
        PresenterRegistry $presenterRegistry,
        AuthorizationCheckerInterface $authorizationChecker,
        AttributeRepositoryInterface $attributeRepository,
        ProductDraftChangesPermissionHelper $permissionHelper,
        ValueCollectionWithoutEmptyValuesProvider $valueCollectionWithoutEmptyValuesProvider,
        EntityWithValuesDraftInterface $entityWithValuesDraft,
        EntityWithValuesInterface $entityWithValues,
        AttributeInterface $attribute,
        LocaleRepositoryInterface $localeRepository,
        LocaleInterface $locale
    ) {
        $context = ['locales' => ['en_US']];
        $this->setupInProgressDraft(
            $context,
            $entityWithValuesDraft,
            $entityWithValues,
            $authorizationChecker,
            $attribute,
            $permissionHelper,
            $attributeRepository,
            $valueCollectionWithoutEmptyValuesProvider,
            $presenterRegistry,
            $localeRepository,
            $locale
        );

        $this->normalize($entityWithValuesDraft, $context)->shouldReturn([
            'status_label' => 'ready',
            'status' => 'ready',
            'search_id' => 'product_69',
            'changes' => [
                'name' => [[
                    'before' => 'before proposal',
                    'after' => 'proposal data',
                    'data' => 'proposal data',
                    'attributeLabel' => 'Name',
                    'attributeType' => "pim_catalog_text",
                    'attributeReferenceDataName' => "refdataname",
                    'scope' => null,
                    'locale' => null,
                    'canReview' => true
                ]]
            ],
            'author_code' => 'mary',
            'approve' => true,
            'refuse' => true,
            'id' => 42,
        ]);
    }

    function it_normalizes_when_user_is_not_owner(
        PresenterRegistry $presenterRegistry,
        AuthorizationCheckerInterface $authorizationChecker,
        AttributeRepositoryInterface $attributeRepository,
        ProductDraftChangesPermissionHelper $permissionHelper,
        ValueCollectionWithoutEmptyValuesProvider $valueCollectionWithoutEmptyValuesProvider,
        EntityWithValuesDraftInterface $entityWithValuesDraft,
        EntityWithValuesInterface $entityWithValues,
        AttributeInterface $attribute,
        LocaleRepositoryInterface $localeRepository,
        LocaleInterface $locale
    ) {
        $context = ['locales' => ['en_US']];
        $this->setupInProgressDraft(
            $context,
            $entityWithValuesDraft,
            $entityWithValues,
            $authorizationChecker,
            $attribute,
            $permissionHelper,
            $attributeRepository,
            $valueCollectionWithoutEmptyValuesProvider,
            $presenterRegistry,
            $localeRepository,
            $locale,
            ['is_owner' => false]
        );

        $result = $this->normalize($entityWithValuesDraft, $context);
        $result['approve']->shouldEqual(false);
        $result['refuse']->shouldEqual(false);
        $result['changes']['name'][0]['canReview']->shouldEqual(false);
    }

    function it_normalizes_when_user_has_no_permission_to_view_attribute(
        PresenterRegistry $presenterRegistry,
        AuthorizationCheckerInterface $authorizationChecker,
        AttributeRepositoryInterface $attributeRepository,
        ProductDraftChangesPermissionHelper $permissionHelper,
        ValueCollectionWithoutEmptyValuesProvider $valueCollectionWithoutEmptyValuesProvider,
        EntityWithValuesDraftInterface $entityWithValuesDraft,
        EntityWithValuesInterface $entityWithValues,
        AttributeInterface $attribute,
        LocaleRepositoryInterface $localeRepository,
        LocaleInterface $locale
    ) {
        $context = ['locales' => ['en_US']];
        $this->setupInProgressDraft(
            $context,
            $entityWithValuesDraft,
            $entityWithValues,
            $authorizationChecker,
            $attribute,
            $permissionHelper,
            $attributeRepository,
            $valueCollectionWithoutEmptyValuesProvider,
            $presenterRegistry,
            $localeRepository,
            $locale,
            ['view_attribute' => false]
        );

        $result = $this->normalize($entityWithValuesDraft, $context);
        $result['changes']->shouldEqual([]);
    }

    function it_normalizes_when_user_has_no_permission_to_edit_attribute(
        PresenterRegistry $presenterRegistry,
        AuthorizationCheckerInterface $authorizationChecker,
        AttributeRepositoryInterface $attributeRepository,
        ProductDraftChangesPermissionHelper $permissionHelper,
        ValueCollectionWithoutEmptyValuesProvider $valueCollectionWithoutEmptyValuesProvider,
        EntityWithValuesDraftInterface $entityWithValuesDraft,
        EntityWithValuesInterface $entityWithValues,
        AttributeInterface $attribute,
        LocaleRepositoryInterface $localeRepository,
        LocaleInterface $locale
    ) {
        $context = ['locales' => ['en_US']];
        $this->setupInProgressDraft(
            $context,
            $entityWithValuesDraft,
            $entityWithValues,
            $authorizationChecker,
            $attribute,
            $permissionHelper,
            $attributeRepository,
            $valueCollectionWithoutEmptyValuesProvider,
            $presenterRegistry,
            $localeRepository,
            $locale,
            ['edit_attribute' => false]
        );

        $result = $this->normalize($entityWithValuesDraft, $context);
        $result['approve']->shouldEqual(false);
        $result['refuse']->shouldEqual(false);
        $result['changes']['name'][0]['canReview']->shouldEqual(false);
    }

    function it_normalizes_with_locale_permission(
        PresenterRegistry $presenterRegistry,
        AuthorizationCheckerInterface $authorizationChecker,
        AttributeRepositoryInterface $attributeRepository,
        ProductDraftChangesPermissionHelper $permissionHelper,
        ValueCollectionWithoutEmptyValuesProvider $valueCollectionWithoutEmptyValuesProvider,
        EntityWithValuesDraftInterface $entityWithValuesDraft,
        EntityWithValuesInterface $entityWithValues,
        AttributeInterface $attribute,
        LocaleRepositoryInterface $localeRepository,
        LocaleInterface $locale
    ) {
        $context = ['locales' => ['en_US']];
        $this->setupInProgressDraft(
            $context,
            $entityWithValuesDraft,
            $entityWithValues,
            $authorizationChecker,
            $attribute,
            $permissionHelper,
            $attributeRepository,
            $valueCollectionWithoutEmptyValuesProvider,
            $presenterRegistry,
            $localeRepository,
            $locale,
            ['localizable' => true]
        );

        $result = $this->normalize($entityWithValuesDraft, $context);
        $result['changes']['name'][0]['locale']->shouldEqual('en_US');
    }

    function it_normalizes_without_locale_permission_view(
        PresenterRegistry $presenterRegistry,
        AuthorizationCheckerInterface $authorizationChecker,
        AttributeRepositoryInterface $attributeRepository,
        ProductDraftChangesPermissionHelper $permissionHelper,
        ValueCollectionWithoutEmptyValuesProvider $valueCollectionWithoutEmptyValuesProvider,
        EntityWithValuesDraftInterface $entityWithValuesDraft,
        EntityWithValuesInterface $entityWithValues,
        AttributeInterface $attribute,
        LocaleRepositoryInterface $localeRepository,
        LocaleInterface $locale
    ) {
        $context = ['locales' => ['en_US']];
        $this->setupInProgressDraft(
            $context,
            $entityWithValuesDraft,
            $entityWithValues,
            $authorizationChecker,
            $attribute,
            $permissionHelper,
            $attributeRepository,
            $valueCollectionWithoutEmptyValuesProvider,
            $presenterRegistry,
            $localeRepository,
            $locale,
            [
                'localizable' => true,
                'view_locale' => false,
            ]
        );

        $result = $this->normalize($entityWithValuesDraft, $context);
        $result['changes']->shouldEqual([]);
    }

    function it_normalizes_without_locale_permission_edit(
        PresenterRegistry $presenterRegistry,
        AuthorizationCheckerInterface $authorizationChecker,
        AttributeRepositoryInterface $attributeRepository,
        ProductDraftChangesPermissionHelper $permissionHelper,
        ValueCollectionWithoutEmptyValuesProvider $valueCollectionWithoutEmptyValuesProvider,
        EntityWithValuesDraftInterface $entityWithValuesDraft,
        EntityWithValuesInterface $entityWithValues,
        AttributeInterface $attribute,
        LocaleRepositoryInterface $localeRepository,
        LocaleInterface $locale
    ) {
        $context = ['locales' => ['en_US']];
        $this->setupInProgressDraft(
            $context,
            $entityWithValuesDraft,
            $entityWithValues,
            $authorizationChecker,
            $attribute,
            $permissionHelper,
            $attributeRepository,
            $valueCollectionWithoutEmptyValuesProvider,
            $presenterRegistry,
            $localeRepository,
            $locale,
            [
                'localizable' => true,
                'edit_locale' => false,
            ]
        );

        $result = $this->normalize($entityWithValuesDraft, $context);
        $result['changes']['name'][0]['locale']->shouldEqual('en_US');
        $result['changes']['name'][0]['canReview']->shouldEqual(false);
    }

    private function setupInProgressDraft(
        array $context,
        EntityWithValuesDraftInterface $entityWithValuesDraft,
        EntityWithValuesInterface $entityWithValues,
        AuthorizationCheckerInterface $authorizationChecker,
        AttributeInterface $attribute,
        ProductDraftChangesPermissionHelper $permissionHelper,
        AttributeRepositoryInterface $attributeRepository,
        ValueCollectionWithoutEmptyValuesProvider $valueCollectionWithoutEmptyValuesProvider,
        PresenterRegistry $presenterRegistry,
        LocaleRepositoryInterface $localeRepository,
        LocaleInterface $locale,
        array $options = []
    ) {
        $change = [
            'data' => 'proposal data',
            'scope' => null,
            'locale' => isset($options['localizable']) && $options['localizable'] ? 'en_US' : null,
        ];

        $entityWithValuesDraft->getId()->willReturn(42);
        $entityWithValuesDraft->isInProgress()->willReturn(false);
        $entityWithValuesDraft->getEntityWithValue()->willReturn($entityWithValues);
        $entityWithValuesDraft->getChanges()->willReturn(['values' => ['name' => [$change]]]);
        $entityWithValuesDraft->getAuthor()->willReturn('mary');
        $entityWithValuesDraft->getReviewStatusForChange('name', Argument::any(), Argument::any())->willReturn('to_review');

        $entityWithValues->getIdentifier()->willReturn('product_69');

        $authorizationChecker->isGranted('OWN_RESOURCE', $entityWithValues)->willReturn(isset($options['is_owner']) ? $options['is_owner'] : true);
        $authorizationChecker->isGranted('VIEW_ATTRIBUTES', $attribute)->willReturn(isset($options['view_attribute']) ? $options['view_attribute'] : true);
        $authorizationChecker->isGranted('EDIT_ATTRIBUTES', $attribute)->willReturn(isset($options['edit_attribute']) ? $options['edit_attribute'] : true);
        if (isset($options['localizable']) && $options['localizable']) {
            $localeRepository->findOneByIdentifier('en_US')->willReturn($locale);
            $authorizationChecker->isGranted('VIEW_ITEMS', $locale)->willReturn(isset($options['view_locale']) ? $options['view_locale'] : true);
            $authorizationChecker->isGranted('EDIT_ITEMS', $locale)->willReturn(isset($options['edit_locale']) ? $options['edit_locale'] : true);
        }

        $permissionHelper->canEditOneChangeToReview($entityWithValuesDraft)->willReturn(isset($options['edit_attribute']) ? $options['edit_attribute'] : true);
        $permissionHelper->canEditOneChangeDraft($entityWithValuesDraft)->willReturn(isset($options['edit_attribute']) ? $options['edit_attribute'] : true);
        $permissionHelper->canEditAllChangesToReview($entityWithValuesDraft)->willReturn(isset($options['edit_attribute']) ? $options['edit_attribute'] : true);

        $attributeRepository->findOneByIdentifier('name')->willReturn($attribute);
        $attribute->getLabel()->willReturn('Name');
        $attribute->isLocalizable()->willReturn(isset($options['localizable']) ? $options['localizable'] : false);
        $attribute->getType()->willReturn('pim_catalog_text');
        $attribute->getReferenceDataName()->willReturn('refdataname');

        $valueCollectionWithoutEmptyValuesProvider->getChanges($entityWithValuesDraft, $context)->willReturn([
            'name' => [[
                'data' => 'proposal data',
                'scope' => null,
                'locale' => isset($options['localizable']) && $options['localizable'] ? 'en_US' : null,
            ]]
        ]);

        $presenterRegistry->presentChange($entityWithValuesDraft, $change, 'name')->willReturn([
            'before' => 'before proposal',
            'after' => 'proposal data',
        ]);
    }
}
