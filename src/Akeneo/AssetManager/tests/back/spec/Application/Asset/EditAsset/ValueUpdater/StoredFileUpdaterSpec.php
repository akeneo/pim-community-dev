<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditStoredFileValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditTextValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater\StoredFileUpdater;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeAllowedExtensions;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxFileSize;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\ImageAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use PhpParser\Node\Arg;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class StoredFileUpdaterSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(StoredFileUpdater::class);
    }

    function it_only_supports_edit_upload_file_value_command(
        EditTextValueCommand $editTextValueCommand,
        EditStoredFileValueCommand $editStoredFileValueCommand
    ) {
        $this->supports($editTextValueCommand)->shouldReturn(false);
        $this->supports($editStoredFileValueCommand)->shouldReturn(true);
    }

    function it_edits_the_file_value_of_a_asset(
        Asset $asset,
        Value $existingValue,
        FileData $existingFileData
    ) {
        $imageAttribute = $this->getAttribute();

        $command = new EditStoredFileValueCommand(
            $imageAttribute,
            'ecommerce',
            'de_DE',
            '/a/b/c/rillettes.png',
            'rillettes.png',
            2048,
            'image/png',
            'png'
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
        $imageAttribute = $this->getAttribute();

        $command = new EditStoredFileValueCommand(
            $imageAttribute,
            'ecommerce',
            'de_DE',
            '/a/b/c/jambon.png',
            'jambon.png',
            2048,
            'image/png',
            'png'
        );

        $asset->findValue(Argument::type(ValueKey::class))
            ->willReturn($existingValue);

        $existingValue->getData()
            ->willReturn($existingFileData);

        $existingFileData->getKey()->willReturn('/a/b/c/jambon.png');

        $value = Value::create(
            $command->attribute->getIdentifier(),
            ChannelReference::createfromNormalized('ecommerce'),
            LocaleReference::createfromNormalized('de_DE'),
            $existingFileData->getWrappedObject()
        );

        $asset->setValue($value)->shouldBeCalled();

        $this->__invoke($asset, $command);
    }

    private function getAttribute(): ImageAttribute
    {
        $imageAttribute = ImageAttribute::create(
            AttributeIdentifier::create('designer', 'image', 'test'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('image'),
            LabelCollection::fromArray(['fr_FR' => 'Image', 'en_US' => 'Image']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxFileSize::fromString('120'),
            AttributeAllowedExtensions::fromList(AttributeAllowedExtensions::VALID_EXTENSIONS)
        );

        return $imageAttribute;
    }
}
