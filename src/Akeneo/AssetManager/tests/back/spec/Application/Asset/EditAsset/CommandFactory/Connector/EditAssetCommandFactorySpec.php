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

namespace spec\Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\Connector;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\Connector\EditAssetCommandFactory;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditTextValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditValueCommandFactoryInterface;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditValueCommandFactoryRegistryInterface;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EditAssetCommandFactorySpec extends ObjectBehavior
{
    function let(
        EditValueCommandFactoryRegistryInterface $editAssetValueCommandFactoryRegistry,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier
    ) {
        $this->beConstructedWith($editAssetValueCommandFactoryRegistry, $findAttributesIndexedByIdentifier);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EditAssetCommandFactory::class);
    }

    function it_creates_an_edit_asset_command(
        EditValueCommandFactoryRegistryInterface $editAssetValueCommandFactoryRegistry,
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier,
        EditValueCommandFactoryInterface $textValueCommandFactory,
        TextAttribute $descriptionAttribute,
        TextAttribute $numericCodeAttribute,
        EditTextValueCommand $editDescriptionCommand,
        EditTextValueCommand $editNumericCodeAttributeCommand
    ) {
        $normalizedAsset = [
            'code' => 'starck',
            'image' => 'images/starck.jpg',
            'labels' => [
                'en_us' => 'Philippe Starck'
            ],
            'values' => [
                'description' => [
                    [
                        'channel' => 'ecommerce',
                        'locale'  => 'en_US',
                        'data'    => 'an awesome designer'
                    ],
                ],
                '42' => [
                    [
                        'channel' => null,
                        'locale'  => null,
                        'data'    => 'Attribute with a numeric code'
                    ]
                ]
            ],
        ];
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');

        $findAttributesIndexedByIdentifier->find(Argument::type(AssetFamilyIdentifier::class))->willReturn([
            'desginer_description_fingerprint' => $descriptionAttribute,
            'designer_42_fingerprint' => $numericCodeAttribute
        ]);
        $descriptionAttribute->getCode()->willReturn(AttributeCode::fromString('description'));
        $numericCodeAttribute->getCode()->willReturn(AttributeCode::fromString('42'));

        $editAssetValueCommandFactoryRegistry
            ->getFactory($descriptionAttribute, $normalizedAsset['values']['description'][0])
            ->willReturn($textValueCommandFactory);
        $editAssetValueCommandFactoryRegistry
            ->getFactory($numericCodeAttribute, $normalizedAsset['values']['42'][0])
            ->willReturn($textValueCommandFactory);
        $textValueCommandFactory
            ->create($descriptionAttribute, $normalizedAsset['values']['description'][0])
            ->willReturn($editDescriptionCommand);
        $textValueCommandFactory
            ->create($numericCodeAttribute, $normalizedAsset['values']['42'][0])
            ->willReturn($editNumericCodeAttributeCommand);

        $command = $this->create($assetFamilyIdentifier, $normalizedAsset);
        $command->shouldBeAnInstanceOf(EditAssetCommand::class);
        $command->assetFamilyIdentifier->shouldBeEqualTo('designer');
        $command->code->shouldBeEqualTo('starck');
        $command->editAssetValueCommands->shouldBeLike([$editDescriptionCommand, $editNumericCodeAttributeCommand]);
    }

    function it_creates_an_edit_asset_command_without_values()
    {
        $normalizedAsset = [
            'code' => 'starck',
            'labels' => [
                'en_us' => 'Philippe Starck'
            ]
        ];
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');

        $command = $this->create($assetFamilyIdentifier, $normalizedAsset);
        $command->shouldBeAnInstanceOf(EditAssetCommand::class);
        $command->assetFamilyIdentifier->shouldBeEqualTo('designer');
        $command->code->shouldBe('starck');
        $command->editAssetValueCommands->shouldBe([]);
    }

    function it_throws_an_exception_if_an_attribute_to_edit_does_not_exist(
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier,
        TextAttribute $descriptionAttribute
    ) {
        $normalizedAsset = [
            'code' => 'starck',
            'labels' => [
                'en_us' => 'Philippe Starck'
            ],
            'values' => [
                'wrong_attribute' => [
                    'channel'   => null,
                    'locale'    => null,
                    'data'      => 'This attribute does not exist'
                ],
                'description' => [
                    'channel'   => 'ecommerce',
                    'locale'    => 'en_US',
                    'data'      => 'an awesome designer'
                ]
            ]
        ];
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');

        $findAttributesIndexedByIdentifier->find(Argument::type(AssetFamilyIdentifier::class))->willReturn([
            'desginer_description_fingerprint' => $descriptionAttribute
        ]);
        $descriptionAttribute->getCode()->willReturn(AttributeCode::fromString('description'));

        $this->shouldThrow(\InvalidArgumentException::class)->during('create', [
            $assetFamilyIdentifier,
            $normalizedAsset
        ]);
    }
}
