<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditMediaFileValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditTextValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater\MediaFileUpdater;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFile\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MediaFileUpdaterSpec extends ObjectBehavior
{
    private const VALID_EXTENSIONS = ['gif', 'jfif', 'jif', 'jpeg', 'jpg', 'pdf', 'png', 'psd', 'tif', 'tiff'];

    function it_is_initializable()
    {
        $this->shouldHaveType(MediaFileUpdater::class);
    }

    function it_only_supports_edit_upload_file_value_command(
        EditTextValueCommand $editTextValueCommand,
        EditMediaFileValueCommand $editMediaFileValueCommand
    ) {
        $this->supports($editTextValueCommand)->shouldReturn(false);
        $this->supports($editMediaFileValueCommand)->shouldReturn(true);
    }

    function it_edits_the_file_value_of_a_asset(
        Asset $asset,
        Value $existingValue,
        FileData $existingFileData
    ) {
        $mediaFileAttribute = $this->getAttribute();

        $command = new EditMediaFileValueCommand(
            $mediaFileAttribute,
            'ecommerce',
            'de_DE',
            '/a/b/c/rillettes.png',
            'rillettes.png',
            2048,
            'image/png',
            'png',
            '2019-11-22T15:16:21+0000'
        );

        $asset->findValue(Argument::type(ValueKey::class))
            ->willReturn($existingValue);

        $existingValue->getData()
            ->willReturn($existingFileData);

        $existingFileData->getKey()->willReturn('/a/b/c/jambon.png');

        $asset->setValue(Argument::type(Value::class))->shouldBeCalled();

        $this->__invoke($asset, $command);
    }

    function it_sets_the_same_file_data_if_its_the_same_file(
        Asset $asset,
        Value $existingValue,
        FileData $existingFileData
    ) {
        $mediaFileAttribute = $this->getAttribute();

        $command = new EditMediaFileValueCommand(
            $mediaFileAttribute,
            'ecommerce',
            'de_DE',
            '/a/b/c/jambon.png',
            'jambon.png',
            2048,
            'image/png',
            'png',
            '2019-11-22T15:16:21+0000'
        );

        $asset->findValue(Argument::type(ValueKey::class))
            ->willReturn($existingValue);

        $existingValue->getData()
            ->willReturn($existingFileData);

        $existingFileData->getKey()->willReturn('/a/b/c/jambon.png');

        $value = Value::create(
            $command->attribute->getIdentifier(),
            ChannelReference::createFromNormalized('ecommerce'),
            LocaleReference::createFromNormalized('de_DE'),
            $existingFileData->getWrappedObject()
        );

        $asset->setValue($value)->shouldBeCalled();

        $this->__invoke($asset, $command);
    }

    private function getAttribute(): MediaFileAttribute
    {
        $mediaFileAttribute = MediaFileAttribute::create(
            AttributeIdentifier::create('designer', 'image', 'test'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('image'),
            LabelCollection::fromArray(['fr_FR' => 'Image', 'en_US' => 'Image']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxFileSize::fromString('120'),
            AttributeAllowedExtensions::fromList(self::VALID_EXTENSIONS),
            MediaType::fromString(MediaType::IMAGE)
        );

        return $mediaFileAttribute;
    }
}
