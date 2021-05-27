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

namespace spec\Akeneo\AssetManager\Application\Asset\EditAsset;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditTextValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\EditAssetHandler;
use Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater\ValueUpdaterInterface;
use Akeneo\AssetManager\Application\Asset\EditAsset\ValueUpdater\ValueUpdaterRegistryInterface;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class EditAssetHandlerSpec extends ObjectBehavior
{
    function let(
        ValueUpdaterRegistryInterface $valueUpdaterRegistry,
        AssetRepositoryInterface $assetRepository,
        FileStorerInterface $storer
    ) {
        $this->beConstructedWith($valueUpdaterRegistry, $assetRepository, $storer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(EditAssetHandler::class);
    }

    function it_edits_a_asset(
        ValueUpdaterRegistryInterface $valueUpdaterRegistry,
        AssetRepositoryInterface $assetRepository,
        Asset $asset,
        ValueUpdaterInterface $textUpdater
    ) {
        $textAttribute = $this->getAttribute();

        $editDescriptionCommand = new EditTextValueCommand(
            $textAttribute,
            null,
            'fr_FR',
            'Sony is a famous electronic company'
        );

        $editAssetCommand = new EditAssetCommand(
            'brand',
            'sony',
            [$editDescriptionCommand]
        );

        $assetRepository->getByAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString('brand'),
            AssetCode::fromString('sony')
        )->willReturn($asset);
        $valueUpdaterRegistry->getUpdater($editDescriptionCommand)->willReturn($textUpdater);

        $textUpdater->__invoke($asset, $editDescriptionCommand)->shouldBeCalled();
        $assetRepository->update($asset)->shouldBeCalled();

        $this->__invoke($editAssetCommand);
    }

    private function getAttribute(): TextAttribute
    {
        return TextAttribute::createText(
            AttributeIdentifier::create('designer', 'name', 'test'),
            AssetFamilyIdentifier::fromString('designer'),
            AttributeCode::fromString('name'),
            LabelCollection::fromArray(['fr_FR' => 'Nom', 'en_US' => 'Name']),
            AttributeOrder::fromInteger(0),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(true),
            AttributeValuePerLocale::fromBoolean(true),
            AttributeMaxLength::fromInteger(300),
            AttributeValidationRule::none(),
            AttributeRegularExpression::createEmpty()
        );
    }
}
