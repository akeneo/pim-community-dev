<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\Connector;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\AbstractEditValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditMediaFileValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditMediaFileValueCommandFactory;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditValueCommandFactoryRegistryInterface;
use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\Connector\EditAssetCommandFactory;
use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\EditAssetValueCommandsFactory;
use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\Exception\InvalidNamingConventionSourceAttributeType;
use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\Exception\NamingConventionException;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConvention;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\Source;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\NumberAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class EditAssetCommandFactorySpec extends ObjectBehavior
{
    function let(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AttributeRepositoryInterface $attributeRepository,
        EditAssetValueCommandsFactory $editAssetValueCommandsFactory,
        EditValueCommandFactoryRegistryInterface $editValueCommandFactoryRegistry
    ) {
        $this->beConstructedWith(
            $assetFamilyRepository,
            $attributeRepository,
            $editAssetValueCommandsFactory,
            $editValueCommandFactoryRegistry
        );
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(EditAssetCommandFactory::class);
    }

    function it_returns_a_edit_asset_command_with_code_as_naming_convention_source(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        EditAssetValueCommandsFactory $editAssetValueCommandsFactory,
        AssetFamily $assetFamily,
        NamingConvention $namingConvention,
        Source $source,
        AbstractEditValueCommand $editAssetValueCommand
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('family');
        $assetFamilyRepository->getByIdentifier($assetFamilyIdentifier)->willReturn($assetFamily);
        $assetFamily->getNamingConvention()->willReturn($namingConvention);
        $namingConvention->getSource()->willReturn($source);
        $source->isAssetCode()->willReturn(true);

        $editAssetValueCommandsFactory->create($assetFamilyIdentifier, $namingConvention, 'the_code')
            ->willReturn([$editAssetValueCommand]);

        $normalizedCommand = [
            'asset_family_identifier' => 'family',
            'code' => 'the_code',
            'values' => [],
        ];
        $editAssetCommand = $this->create($normalizedCommand, $assetFamilyIdentifier);
        $editAssetCommand->shouldBeAnInstanceOf(EditAssetCommand::class);
        $editAssetCommand->assetFamilyIdentifier->shouldBe('family');
        $editAssetCommand->code->shouldBe('the_code');
        $editAssetCommand->editAssetValueCommands->shouldBe([$editAssetValueCommand]);
    }

    function it_returns_a_edit_asset_command_with_media_file_as_naming_convention_source(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AttributeRepositoryInterface $attributeRepository,
        EditAssetValueCommandsFactory $editAssetValueCommandsFactory,
        AssetFamily $assetFamily,
        NamingConvention $namingConvention,
        Source $source,
        MediaFileAttribute $attribute,
        AbstractEditValueCommand $editAssetValueCommand,
        EditValueCommandFactoryRegistryInterface $editValueCommandFactoryRegistry,
        EditMediaFileValueCommandFactory $editMediaFileValueCommandFactory
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('family');
        $assetFamilyRepository->getByIdentifier($assetFamilyIdentifier)->willReturn($assetFamily);
        $assetFamily->getNamingConvention()->willReturn($namingConvention);
        $namingConvention->getSource()->willReturn($source);
        $source->isAssetCode()->willReturn(false);
        $source->getProperty()->willReturn('image');
        $source->getChannelReference()->willReturn(ChannelReference::createFromNormalized(null));
        $source->getLocaleReference()->willReturn(LocaleReference::createFromNormalized(null));
        $editImageCommand = new EditMediaFileValueCommand($attribute->getWrappedObject(), null, null, 'path/to/my_file.png', 'my_file.png', null, null, null, null);

        $attributeRepository->getByCodeAndAssetFamilyIdentifier(AttributeCode::fromString('image'), $assetFamilyIdentifier)
            ->willReturn($attribute);

        $editAssetValueCommandsFactory->create($assetFamilyIdentifier, $namingConvention, 'my_file.png')
            ->willReturn([$editAssetValueCommand]);

        $normalizedCommand = [
            'code' => 'the_code',
            'values' => [
                'image' => [
                    [
                        'channel' => null,
                        'locale' => null,
                        'data' => 'path/to/my_file.png',
                    ]
                ],
            ],
        ];

        $editValueCommandFactoryRegistry->getFactory($attribute, $normalizedCommand['values']['image'][0])->willReturn($editMediaFileValueCommandFactory);
        $editMediaFileValueCommandFactory->create($attribute, $normalizedCommand['values']['image'][0])->willReturn($editImageCommand);

        $editAssetCommand = $this->create($normalizedCommand, $assetFamilyIdentifier);
        $editAssetCommand->shouldBeAnInstanceOf(EditAssetCommand::class);
        $editAssetCommand->assetFamilyIdentifier->shouldBe('family');
        $editAssetCommand->code->shouldBe('the_code');
        $editAssetCommand->editAssetValueCommands->shouldBe([$editAssetValueCommand]);
    }

    function it_returns_a_edit_asset_command_with_media_link_as_naming_convention_source(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AttributeRepositoryInterface $attributeRepository,
        EditAssetValueCommandsFactory $editAssetValueCommandsFactory,
        AssetFamily $assetFamily,
        NamingConvention $namingConvention,
        Source $source,
        MediaLinkAttribute $attribute,
        AbstractEditValueCommand $editAssetValueCommand
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('family');
        $assetFamilyRepository->getByIdentifier($assetFamilyIdentifier)->willReturn($assetFamily);
        $assetFamily->getNamingConvention()->willReturn($namingConvention);
        $namingConvention->getSource()->willReturn($source);
        $source->isAssetCode()->willReturn(false);
        $source->getProperty()->willReturn('image');
        $source->getChannelReference()->willReturn(ChannelReference::createFromNormalized(null));
        $source->getLocaleReference()->willReturn(LocaleReference::createFromNormalized(null));

        $attributeRepository->getByCodeAndAssetFamilyIdentifier(AttributeCode::fromString('image'), $assetFamilyIdentifier)
            ->willReturn($attribute);

        $editAssetValueCommandsFactory->create($assetFamilyIdentifier, $namingConvention, 'the_link')
            ->willReturn([$editAssetValueCommand]);

        $normalizedCommand = [
            'code' => 'the_code',
            'values' => [
                'image' => [
                    [
                        'channel' => 'ecommerce',
                        'locale' => null,
                        'data' => 'the_skipped_link',
                    ],
                    [
                        'channel' => null,
                        'locale' => null,
                        'data' => 'the_link',
                    ],
                ],
            ],
        ];
        $editAssetCommand = $this->create($normalizedCommand, $assetFamilyIdentifier);
        $editAssetCommand->shouldBeAnInstanceOf(EditAssetCommand::class);
        $editAssetCommand->assetFamilyIdentifier->shouldBe('family');
        $editAssetCommand->code->shouldBe('the_code');
        $editAssetCommand->editAssetValueCommands->shouldBe([$editAssetValueCommand]);
    }

    function it_returns_a_edit_asset_command_with_text_as_naming_convention_source(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AttributeRepositoryInterface $attributeRepository,
        EditAssetValueCommandsFactory $editAssetValueCommandsFactory,
        AssetFamily $assetFamily,
        NamingConvention $namingConvention,
        Source $source,
        TextAttribute $attribute,
        AbstractEditValueCommand $editAssetValueCommand
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('family');
        $assetFamilyRepository->getByIdentifier($assetFamilyIdentifier)->willReturn($assetFamily);
        $assetFamily->getNamingConvention()->willReturn($namingConvention);
        $namingConvention->getSource()->willReturn($source);
        $source->isAssetCode()->willReturn(false);
        $source->getProperty()->willReturn('image');
        $source->getChannelReference()->willReturn(ChannelReference::createFromNormalized(null));
        $source->getLocaleReference()->willReturn(LocaleReference::createFromNormalized(null));

        $attributeRepository->getByCodeAndAssetFamilyIdentifier(AttributeCode::fromString('image'), $assetFamilyIdentifier)
            ->willReturn($attribute);

        $editAssetValueCommandsFactory->create($assetFamilyIdentifier, $namingConvention, 'the_text')
            ->willReturn([$editAssetValueCommand]);

        $normalizedCommand = [
            'asset_family_identifier' => 'family',
            'code' => 'the_code',
            'values' => [
                'image' => [
                    [
                        'channel' => 'ecommerce',
                        'locale' => null,
                        'data' => 'the_skipped_text',
                    ],
                    [
                        'channel' => null,
                        'locale' => null,
                        'data' => 'the_text',
                    ],
                ],
            ],
        ];
        $editAssetCommand = $this->create($normalizedCommand, $assetFamilyIdentifier);
        $editAssetCommand->shouldBeAnInstanceOf(EditAssetCommand::class);
        $editAssetCommand->assetFamilyIdentifier->shouldBe('family');
        $editAssetCommand->code->shouldBe('the_code');
        $editAssetCommand->editAssetValueCommands->shouldBe([$editAssetValueCommand]);
    }

    function it_throws_an_exception_when_naming_convention_source_attribute_can_not_be_handled(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AttributeRepositoryInterface $attributeRepository,
        EditAssetValueCommandsFactory $editAssetValueCommandsFactory,
        AssetFamily $assetFamily,
        NamingConvention $namingConvention,
        Source $source,
        NumberAttribute $attribute,
        AbstractEditValueCommand $editAssetValueCommand
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('family');
        $assetFamilyRepository->getByIdentifier($assetFamilyIdentifier)->willReturn($assetFamily);
        $assetFamily->getNamingConvention()->willReturn($namingConvention);
        $namingConvention->getSource()->willReturn($source);
        $namingConvention->abortAssetCreationOnError()->willReturn(false);
        $source->isAssetCode()->willReturn(false);
        $source->getProperty()->willReturn('image');
        $source->getChannelReference()->willReturn(ChannelReference::createFromNormalized(null));
        $source->getLocaleReference()->willReturn(LocaleReference::createFromNormalized(null));

        $attributeRepository->getByCodeAndAssetFamilyIdentifier(AttributeCode::fromString('image'), $assetFamilyIdentifier)
            ->willReturn($attribute);

        $editAssetValueCommandsFactory->create($assetFamilyIdentifier, $namingConvention, 'the_text')
            ->willReturn([$editAssetValueCommand]);

        $normalizedCommand = [
            'asset_family_identifier' => 'family',
            'code' => 'the_code',
            'values' => [
                'image' => [
                    [
                        'channel' => 'ecommerce',
                        'locale' => null,
                        'data' => 'the_skipped_text',
                    ],
                    [
                        'channel' => null,
                        'locale' => null,
                        'data' => 'the_text',
                    ],
                ],
            ],
        ];
        $this->shouldThrow(NamingConventionException::class)
            ->during('create', [$normalizedCommand, $assetFamilyIdentifier]);

        try {
            $this->create($normalizedCommand, $assetFamilyIdentifier);
        } catch (NamingConventionException $e) {
            Assert::isInstanceOf($e->getEmbeddedException(), InvalidNamingConventionSourceAttributeType::class);
        }
    }
}
