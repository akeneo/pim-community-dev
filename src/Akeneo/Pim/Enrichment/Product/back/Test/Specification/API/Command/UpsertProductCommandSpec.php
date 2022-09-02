<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\AddMultiSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ClearValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\AddToGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Groups\SetGroups;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\AssociateQuantifiedProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedAssociationUserIntentCollection;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetAssetValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetBooleanValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetDateValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetEnabled;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMeasurementValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetNumberValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleReferenceEntityValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextValue;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductUuid;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpsertProductCommandSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(UpsertProductCommand::class);
    }

    function it_can_be_constructed_with_value_intents()
    {
        $valueUserIntents = [
            new SetTextValue('name', null, null, 'foo'),
            new SetNumberValue('name', null, null, '10'),
            new SetMeasurementValue('power', null, null, '100', 'KILOWATT'),
            new SetTextareaValue('name', null, null, "<p><span style=\"font-weight: bold;\">title</span></p><p>text</p>"),
            new ClearValue('name', null, null),
            new SetBooleanValue('name', null, null, true),
            new SetDateValue('name', null, null, new \DateTime("2022-03-04T09:35:24+00:00")),
            new AddMultiSelectValue('name', null, null, ['optionA']),
            new SetSimpleReferenceEntityValue('name', null, null, 'Akeneo'),
        ];

        $this->beConstructedThrough('createFromCollection', [1, 'identifier1', $valueUserIntents]);

        $identifier = ProductIdentifier::fromIdentifier('identifier1');

        $this->userId()->shouldReturn(1);
        $this->productIdentifierOrUuid()->shouldBeLike($identifier);
        $this->productIdentifierOrUuid()->identifier()->shouldReturn('identifier1');
        $this->valueUserIntents()->shouldReturn($valueUserIntents);
    }

    function it_cannot_be_constructed_with_bad_value_user_intent()
    {
        $this->beConstructedThrough('createFromCollection', [1, '', [new \stdClass]]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_can_be_constructed_with_field_user_intents()
    {
        $familyUserIntent = new SetFamily('accessories');
        $categoryUserIntent = new SetCategories(['master']);

        $this->beConstructedThrough('createFromCollection', [1, 'identifier1', [$familyUserIntent, $categoryUserIntent]]);

        $identifier = ProductIdentifier::fromIdentifier('identifier1');

        $this->userId()->shouldReturn(1);
        $this->productIdentifierOrUuid()->shouldBeLike($identifier);
        $this->productIdentifierOrUuid()->identifier()->shouldReturn('identifier1');
        $this->familyUserIntent()->shouldReturn($familyUserIntent);
        $this->categoryUserIntent()->shouldReturn($categoryUserIntent);
        $this->valueUserIntents()->shouldReturn([]);
    }

    function it_can_be_constructed_from_a_collection_of_user_intents()
    {
        $familyUserIntent = new SetFamily('accessories');
        $categoryUserIntent = new SetCategories(['master']);
        $setTextValue = new SetTextValue('name', null, null, 'foo');
        $setNumberValue = new SetNumberValue('name', null, null, '10');
        $setDateValue = new SetDateValue('name', null, null, new \DateTime("2022-03-04T09:35:24+00:00"));
        $addMultiSelectValue = new AddMultiSelectValue('name', null, null, ['optionA']);
        $setAssetValue = new SetAssetValue('name', null, null, ['packshot1']);
        $setGroupsIntent = new SetGroups(['groupA', 'groupB']);
        $associateQuantifiedProducts = new AssociateQuantifiedProducts('X_SELL', [new QuantifiedEntity('foo', 5)]);

        $this->beConstructedThrough('createFromCollection', [
            10,
            'identifier1',
            [
                $familyUserIntent,
                $setTextValue,
                $setNumberValue,
                $setDateValue,
                $addMultiSelectValue,
                $setAssetValue,
                $categoryUserIntent,
                $setGroupsIntent,
                $associateQuantifiedProducts,
            ]
        ]);

        $identifier = ProductIdentifier::fromIdentifier('identifier1');

        $this->userId()->shouldReturn(10);
        $this->productIdentifierOrUuid()->shouldBeLike($identifier);
        $this->productIdentifierOrUuid()->identifier()->shouldReturn('identifier1');
        $this->familyUserIntent()->shouldReturn($familyUserIntent);
        $this->categoryUserIntent()->shouldReturn($categoryUserIntent);
        $this->groupUserIntent()->shouldReturn($setGroupsIntent);
        $this->valueUserIntents()->shouldReturn([$setTextValue, $setNumberValue, $setDateValue, $addMultiSelectValue, $setAssetValue]);
        $quantifiedAssociations = $this->quantifiedAssociationUserIntents();
        $quantifiedAssociations->shouldHaveType(QuantifiedAssociationUserIntentCollection::class);
        $quantifiedAssociations->quantifiedAssociationUserIntents()->shouldBe([$associateQuantifiedProducts]);
    }

    function it_cannot_be_constructed_with_multiple_set_enabled_intents()
    {
        $this->beConstructedThrough('createFromCollection', [
            1,
            'identifier1',
            [
                new SetEnabled(true),
                new SetEnabled(false),
            ]
        ]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_constructed_with_multiple_set_categories_intents()
    {
        $this->beConstructedThrough('createFromCollection', [
            1,
            'identifier1',
            [
                new SetCategories(['foo']),
                new SetCategories(['bar']),
            ]
        ]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_constructed_with_multiple_groups_intents()
    {
        $this->beConstructedThrough('createFromCollection', [
            1,
            'identifier1',
            [
                new SetGroups(['foo']),
                new AddToGroups(['bar']),
            ]
        ]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_can_be_constructed_with_product_uuid()
    {
        $uuid = Uuid::uuid4();
        $productUuid = ProductUuid::fromUuid($uuid);

        $this->beConstructedThrough('createWithUuid', [
            1,
            $productUuid,
            []
        ]);

        $this->userId()->shouldReturn(1);
        $this->productIdentifierOrUuid()->shouldBeLike($productUuid);
        $this->productIdentifierOrUuid()->uuid()->shouldReturn($uuid);
    }

    function it_can_be_constructed_with_identifier()
    {
        $productIdentifier = ProductIdentifier::fromIdentifier('identifier1');

        $this->beConstructedThrough('createWithIdentifier', [
            1,
            $productIdentifier,
            []
        ]);

        $this->userId()->shouldReturn(1);
        $this->productIdentifierOrUuid()->shouldBeLike($productIdentifier);
        $this->productIdentifierOrUuid()->identifier()->shouldReturn('identifier1');
    }
}
