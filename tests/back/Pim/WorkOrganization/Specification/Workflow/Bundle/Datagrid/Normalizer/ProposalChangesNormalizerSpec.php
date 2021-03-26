<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Normalizer;

use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Helper\ProductDraftChangesPermissionHelper;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Twig\ProductDraftChangesExtension;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Twig\ProductDraftStatusGridExtension;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProposalChangesNormalizerSpec extends ObjectBehavior
{
    function let(
        NormalizerInterface $standardNormalizer,
        ValueFactory $valueFactory,
        ProductDraftChangesExtension $changesExtension,
        AuthorizationCheckerInterface $authorizationChecker,
        AttributeRepositoryInterface $attributeRepository,
        LocaleRepositoryInterface $localeRepository,
        ProductDraftChangesPermissionHelper $permissionHelper,
        GetAttributes $getAttributes
    ) {
        $this->beConstructedWith(
            $standardNormalizer,
            $valueFactory,
            $changesExtension,
            $authorizationChecker,
            $attributeRepository,
            $localeRepository,
            $permissionHelper,
            $getAttributes
        );
    }

    function it_normalizes_in_progress_draft(
        AuthorizationCheckerInterface $authorizationChecker,
        ProductDraftChangesPermissionHelper $permissionHelper,
        EntityWithValuesDraftInterface $entityWithValuesDraft,
        EntityWithValuesInterface $entityWithValues
    ) {
        $context = ['locales' => ['en_US']];
        $entityWithValuesDraft->getStatus()->willReturn(0); // in progress
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
        NormalizerInterface $standardNormalizer,
        ValueFactory $valueFactory,
        ProductDraftChangesExtension $changesExtension,
        AuthorizationCheckerInterface $authorizationChecker,
        AttributeRepositoryInterface $attributeRepository,
        ProductDraftChangesPermissionHelper $permissionHelper,
        GetAttributes $getAttributes,
        EntityWithValuesDraftInterface $entityWithValuesDraft,
        EntityWithValuesInterface $entityWithValues,
        AttributeInterface $attribute,
        ValueInterface $value
    ) {
        $context = ['locales' => ['en_US']];
        $change = [
            'data' => 'proposal data',
            'scope' => null,
            'locale' => null,
        ];

        $entityWithValuesDraft->getId()->willReturn(42);
        $entityWithValuesDraft->getStatus()->willReturn(1); // ready
        $entityWithValuesDraft->isInProgress()->willReturn(false);
        $entityWithValuesDraft->getEntityWithValue()->willReturn($entityWithValues);
        $entityWithValuesDraft->getChanges()->willReturn(['values' => ['name' => [$change]]]);
        $entityWithValuesDraft->getAuthor()->willReturn('mary');
        $entityWithValues->getIdentifier()->willReturn('product_69');
        $entityWithValuesDraft->getReviewStatusForChange('name', null, null)->willReturn('to_review');

        $authorizationChecker->isGranted('OWN_RESOURCE', $entityWithValues)->willReturn(true);
        $authorizationChecker->isGranted('VIEW_ATTRIBUTES', $attribute)->willReturn(true);
        $authorizationChecker->isGranted('EDIT_ATTRIBUTES', $attribute)->willReturn(true);
        $permissionHelper->canEditOneChangeToReview($entityWithValuesDraft)->willReturn(true);
        $permissionHelper->canEditOneChangeDraft($entityWithValuesDraft)->willReturn(true);
        $permissionHelper->canEditAllChangesToReview($entityWithValuesDraft)->willReturn(true);

        $attributeRepository->findOneByIdentifier('name')->willReturn($attribute);
        $attribute->getLabel()->willReturn('Name');
        $attribute->isLocalizable()->willReturn(false);

        $getAttribute = new Attribute(
            'name',
            'pim_catalog_text',
            [],
            false,
            false,
            null,
            null,
            null,
            'text',
            []
        );
        $getAttributes->forCode('name')->willReturn($getAttribute);

        $value->getAttributeCode()->willReturn('name');
        $value->getScopeCode()->willReturn(null);
        $value->getLocaleCode()->willReturn(null);
        $valueFactory->createByCheckingData($getAttribute, null, null, 'proposal data')->willReturn($value);

        $standardNormalizer->normalize(Argument::type(WriteValueCollection::class), 'standard', $context)->willReturn([
            'name' => [$change]
        ]);

        $changesExtension->presentChange($entityWithValuesDraft, $change, 'name')->willReturn([
            'before' => 'before proposal',
            'after' => 'proposal data',
        ]);

        $this->normalize($entityWithValuesDraft, $context)->shouldReturn([
            'status_label' => 'ready',
            'status' => 'ready',
            'search_id' => 'product_69',
            'changes' => [
                'name' => [
                    [
                        'before' => 'before proposal',
                        'after' => 'proposal data',
                        'data' => 'proposal data',
                        'attributeLabel' => 'Name',
                        'scope' => null,
                        'locale' => null,
                        'canReview' => true
                    ],
                ]
            ],
            'author_code' => 'mary',
            'approve' => true,
            'refuse' => true,
            'id' => 42,
        ]);
    }
}
