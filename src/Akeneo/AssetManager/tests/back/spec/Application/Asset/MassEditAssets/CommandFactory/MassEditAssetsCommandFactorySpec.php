<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Asset\MassEditAssets\CommandFactory;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCommandFactory;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditOptionCollectionValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditOptionCollectionValueCommandFactory;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditTextValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditTextValueCommandFactory;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditValueCommandFactoryRegistryInterface;
use Akeneo\AssetManager\Application\Asset\MassEditAssets\CommandFactory\MassEditAssetsCommandFactory;
use Akeneo\AssetManager\Application\Asset\MassEditAssets\MassEditAssetsCommand;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Transformation\CheckIfTransformationTarget;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MassEditAssetsCommandFactorySpec extends ObjectBehavior
{
    function let(
        EditValueCommandFactoryRegistryInterface $editValueCommandFactoryRegistry,
        FindAttributesIndexedByIdentifierInterface $sqlFindAttributesIndexedByIdentifier,
        CheckIfTransformationTarget $checkIfTransformationTarget
    ) {
        $this->beConstructedWith(
            $editValueCommandFactoryRegistry,
            $sqlFindAttributesIndexedByIdentifier,
            $checkIfTransformationTarget
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(MassEditAssetsCommandFactory::class);
    }

    function it_creates_mass_edit_commands_from_normalized_updaters(
        AssetQuery $query,
        EditValueCommandFactoryRegistryInterface $editValueCommandFactoryRegistry,
        FindAttributesIndexedByIdentifierInterface $sqlFindAttributesIndexedByIdentifier,
        EditOptionCollectionValueCommand $optionCollectionValueCommand,
        EditOptionCollectionValueCommandFactory $optionCollectionValueCommandFactory,
        EditTextValueCommandFactory $textValueCommandFactory,
        EditTextValueCommand $textValueCommand,
        AbstractAttribute $tagsAttribute,
        AbstractAttribute $labelAttribute,
        CheckIfTransformationTarget $checkIfTransformationTarget
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('packshot');
        $query->normalize()->willReturn([]);
        $updaters = [
            [
                'attribute' => 'label_atmosphere_c0496044-8201-47ae-ba3c-847e800af3e9',
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'data' => 'nice update',
                'action' => 'replace',
                'id' => 'some_uuid'
            ],
            [
                'attribute' => 'tags_atmosphere_6cbcbd63-4f9c-4dbb-b519-a3aa8bc9f97e',
                'channel' => 'ecommerce',
                'locale' => null,
                'data' => ['some', 'options'],
                'action' => 'append',
                'id' => 'another_uuid'
            ]
        ];

        $labelAttribute->getIdentifier()->willReturn('label');
        $tagsAttribute->getIdentifier()->willReturn('tag');
        $sqlFindAttributesIndexedByIdentifier->find($assetFamilyIdentifier)->shouldBeCalled()->willReturn([
            'label_atmosphere_c0496044-8201-47ae-ba3c-847e800af3e9' => $labelAttribute->getWrappedObject(),
            'tags_atmosphere_6cbcbd63-4f9c-4dbb-b519-a3aa8bc9f97e' => $tagsAttribute->getWrappedObject(),
        ]);

        $checkIfTransformationTarget->forAttribute($labelAttribute, 'en_US', 'ecommerce')->willReturn(false);
        $checkIfTransformationTarget->forAttribute($tagsAttribute, null, 'ecommerce')->willReturn(false);

        $optionCollectionValueCommandFactory
            ->create($tagsAttribute, [
                'attribute' => 'tags_atmosphere_6cbcbd63-4f9c-4dbb-b519-a3aa8bc9f97e',
                'channel' => 'ecommerce',
                'locale' => null,
                'data' => ['some', 'options'],
                'action' => 'append',
            ])
            ->shouldBeCalled()
            ->willReturn($optionCollectionValueCommand);

        $editValueCommandFactoryRegistry
            ->getFactory($tagsAttribute, [
                'attribute' => 'tags_atmosphere_6cbcbd63-4f9c-4dbb-b519-a3aa8bc9f97e',
                'channel' => 'ecommerce',
                'locale' => null,
                'data' => ['some', 'options'],
                'action' => 'append',
            ])
            ->shouldBeCalled()
            ->willReturn($optionCollectionValueCommandFactory);

        $textValueCommandFactory
            ->create($labelAttribute, [
                'attribute' => 'label_atmosphere_c0496044-8201-47ae-ba3c-847e800af3e9',
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'data' => 'nice update',
                'action' => 'replace',
            ])
            ->shouldBeCalled()
            ->willReturn($textValueCommand);

        $editValueCommandFactoryRegistry
            ->getFactory($labelAttribute, [
                'attribute' => 'label_atmosphere_c0496044-8201-47ae-ba3c-847e800af3e9',
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'data' => 'nice update',
                'action' => 'replace',
            ])
            ->shouldBeCalled()
            ->willReturn($textValueCommandFactory);

        $massEditAssetsCommand = $this->create($assetFamilyIdentifier, $query, $updaters);
        $massEditAssetsCommand->shouldBeLike(new MassEditAssetsCommand(
            'packshot',
            [],
            [
                'some_uuid' => $textValueCommand->getWrappedObject(),
                'another_uuid' => $optionCollectionValueCommand->getWrappedObject(),
            ]
        ));
    }

    function it_should_skip_not_existing_attribute(
        AssetQuery $query,
        EditValueCommandFactoryRegistryInterface $editValueCommandFactoryRegistry,
        FindAttributesIndexedByIdentifierInterface $sqlFindAttributesIndexedByIdentifier,
        EditTextValueCommandFactory $textValueCommandFactory,
        EditTextValueCommand $textValueCommand,
        AbstractAttribute $tagsAttribute,
        AbstractAttribute $labelAttribute,
        CheckIfTransformationTarget $checkIfTransformationTarget
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('packshot');
        $query->normalize()->willReturn([]);
        $updaters = [
            [
                'attribute' => 'label_atmosphere_c0496044-8201-47ae-ba3c-847e800af3e9',
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'data' => 'nice update',
                'action' => 'replace',
                'id' => 'some_uuid'
            ],
            [
                'attribute' => 'tags_atmosphere_6cbcbd63-4f9c-4dbb-b519-a3aa8bc9f97e',
                'channel' => 'ecommerce',
                'locale' => null,
                'data' => ['some', 'options'],
                'action' => 'append',
                'id' => 'another_uuid'
            ]
        ];

        $labelAttribute->getIdentifier()->willReturn('label');
        $tagsAttribute->getIdentifier()->willReturn('tag');
        $sqlFindAttributesIndexedByIdentifier->find($assetFamilyIdentifier)->shouldBeCalled()->willReturn([
            'label_atmosphere_c0496044-8201-47ae-ba3c-847e800af3e9' => $labelAttribute->getWrappedObject(),
        ]);

        $checkIfTransformationTarget->forAttribute($labelAttribute, 'en_US', 'ecommerce')->willReturn(false);

        $textValueCommandFactory
            ->create($labelAttribute, [
                'attribute' => 'label_atmosphere_c0496044-8201-47ae-ba3c-847e800af3e9',
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'data' => 'nice update',
                'action' => 'replace',
            ])
            ->shouldBeCalled()
            ->willReturn($textValueCommand);

        $editValueCommandFactoryRegistry
            ->getFactory($labelAttribute, [
                'attribute' => 'label_atmosphere_c0496044-8201-47ae-ba3c-847e800af3e9',
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'data' => 'nice update',
                'action' => 'replace',
            ])
            ->shouldBeCalled()
            ->willReturn($textValueCommandFactory);

        $massEditAssetsCommand = $this->create($assetFamilyIdentifier, $query, $updaters);
        $massEditAssetsCommand->shouldBeLike(new MassEditAssetsCommand(
            'packshot',
            [],
            [
                'some_uuid' => $textValueCommand->getWrappedObject(),
            ]
        ));
    }

    function it_should_skip_attribute_targeted_by_transformation(
        AssetQuery $query,
        EditValueCommandFactoryRegistryInterface $editValueCommandFactoryRegistry,
        FindAttributesIndexedByIdentifierInterface $sqlFindAttributesIndexedByIdentifier,
        EditTextValueCommandFactory $textValueCommandFactory,
        EditTextValueCommand $textValueCommand,
        AbstractAttribute $tagsAttribute,
        AbstractAttribute $labelAttribute,
        CheckIfTransformationTarget $checkIfTransformationTarget
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('packshot');
        $query->normalize()->willReturn([]);
        $updaters = [
            [
                'attribute' => 'label_atmosphere_c0496044-8201-47ae-ba3c-847e800af3e9',
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'data' => 'nice update',
                'action' => 'replace',
                'id' => 'some_uuid'
            ],
            [
                'attribute' => 'tags_atmosphere_6cbcbd63-4f9c-4dbb-b519-a3aa8bc9f97e',
                'channel' => 'ecommerce',
                'locale' => null,
                'data' => ['some', 'options'],
                'action' => 'append',
                'id' => 'another_uuid'
            ]
        ];

        $labelAttribute->getIdentifier()->willReturn('label');
        $tagsAttribute->getIdentifier()->willReturn('tag');
        $sqlFindAttributesIndexedByIdentifier->find($assetFamilyIdentifier)->shouldBeCalled()->willReturn([
            'label_atmosphere_c0496044-8201-47ae-ba3c-847e800af3e9' => $labelAttribute->getWrappedObject(),
            'tags_atmosphere_6cbcbd63-4f9c-4dbb-b519-a3aa8bc9f97e' => $tagsAttribute->getWrappedObject(),
        ]);

        $checkIfTransformationTarget->forAttribute($labelAttribute, 'en_US', 'ecommerce')->willReturn(false);
        $checkIfTransformationTarget->forAttribute($tagsAttribute, null, 'ecommerce')->willReturn(true);

        $textValueCommandFactory
            ->create($labelAttribute, [
                'attribute' => 'label_atmosphere_c0496044-8201-47ae-ba3c-847e800af3e9',
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'data' => 'nice update',
                'action' => 'replace',
            ])
            ->shouldBeCalled()
            ->willReturn($textValueCommand);

        $editValueCommandFactoryRegistry
            ->getFactory($labelAttribute, [
                'attribute' => 'label_atmosphere_c0496044-8201-47ae-ba3c-847e800af3e9',
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'data' => 'nice update',
                'action' => 'replace',
            ])
            ->shouldBeCalled()
            ->willReturn($textValueCommandFactory);

        $massEditAssetsCommand = $this->create($assetFamilyIdentifier, $query, $updaters);
        $massEditAssetsCommand->shouldBeLike(new MassEditAssetsCommand(
            'packshot',
            [],
            [
                'some_uuid' => $textValueCommand->getWrappedObject(),
            ]
        ));
    }

    function it_should_throw_if_updaters_are_not_valid(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AssetQuery $query
    ) {
        $updaters = [
            [
                'attribute' => [],
                'channel' => 'ecommerce',
                'locale' => 'en_US',
                'data' => 'nice update',
                'action' => 'set'
            ],
        ];

        $this->shouldThrow(new \InvalidArgumentException('Impossible to create a command of mass asset edition.'))->during('create', [$assetFamilyIdentifier, $query, $updaters]);
    }
}
