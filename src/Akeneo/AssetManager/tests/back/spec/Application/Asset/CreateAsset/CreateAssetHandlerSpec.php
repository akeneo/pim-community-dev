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

namespace spec\Akeneo\AssetManager\Application\Asset\CreateAsset;

use Akeneo\AssetManager\Application\Asset\CreateAsset\CreateAssetCommand;
use Akeneo\AssetManager\Application\Asset\CreateAsset\CreateAssetHandler;
use Akeneo\AssetManager\Domain\Event\AssetCreatedEvent;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\TextData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsLabelReference;
use Akeneo\AssetManager\Domain\Query\AssetFamily\FindAssetFamilyAttributeAsLabelInterface;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Webmozart\Assert\Assert;

class CreateAssetHandlerSpec extends ObjectBehavior
{
    function let(
        AssetRepositoryInterface $assetRepository,
        FindAssetFamilyAttributeAsLabelInterface $findAttributeAsLabel
    ) {
        $this->beConstructedWith($assetRepository, $findAttributeAsLabel);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CreateAssetHandler::class);
    }

    function it_creates_and_save_a_new_asset(
        AssetRepositoryInterface $assetRepository,
        CreateAssetCommand $createAssetCommand,
        FindAssetFamilyAttributeAsLabelInterface $findAttributeAsLabel
    ) {
        $createAssetCommand->code = 'intel';
        $createAssetCommand->assetFamilyIdentifier = 'brand';
        $createAssetCommand->labels = [
            'en_US' => 'Intel',
            'fr_FR' => 'Intel',
        ];

        $assetIdentifier = AssetIdentifier::fromString('brand_intel_a1677570-a278-444b-ab46-baa1db199392');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($createAssetCommand->assetFamilyIdentifier);
        $labelAttributeReference = AttributeAsLabelReference::createFromNormalized('label_brand_fingerprint');

        $findAttributeAsLabel
            ->find(Argument::type(AssetFamilyIdentifier::class))
            ->willReturn($labelAttributeReference);

        $assetRepository->nextIdentifier(
            Argument::type(AssetFamilyIdentifier::class),
            Argument::type(AssetCode::class)
        )->willReturn($assetIdentifier);

        $assetRepository->create(Argument::that(function ($asset) use (
            $assetIdentifier,
            $assetFamilyIdentifier,
            $labelAttributeReference
        ) {
            Assert::count($asset->getRecordedEvents(), 1);
            Assert::isInstanceOf(current($asset->getRecordedEvents()), AssetCreatedEvent::class);
            $asset->clearRecordedEvents();

            $expectedAsset = Asset::fromState(
                $assetIdentifier,
                $assetFamilyIdentifier,
                AssetCode::fromString('intel'),
                ValueCollection::fromValues([
                    Value::create(
                        $labelAttributeReference->getIdentifier(),
                        ChannelReference::noReference(),
                        LocaleReference::createFromNormalized('en_US'),
                        TextData::createFromNormalize('Intel')
                    ),
                    Value::create(
                        $labelAttributeReference->getIdentifier(),
                        ChannelReference::noReference(),
                        LocaleReference::createFromNormalized('fr_FR'),
                        TextData::createFromNormalize('Intel')
                    ),
                ]),
                $asset->getCreatedAt(),
                $asset->getUpdatedAt(),
            );

            Assert::eq($expectedAsset, $asset);
            return true;
        }))->shouldBeCalled();

        $this->__invoke($createAssetCommand);
    }

    function it_creates_and_save_a_new_asset_and_ignores_empty_labels(
        AssetRepositoryInterface $assetRepository,
        CreateAssetCommand $createAssetCommand,
        FindAssetFamilyAttributeAsLabelInterface $findAttributeAsLabel
    ) {
        $createAssetCommand->code = 'intel';
        $createAssetCommand->assetFamilyIdentifier = 'brand';
        $createAssetCommand->labels = [
            'en_US' => '',
            'fr_FR' => '',
        ];

        $assetIdentifier = AssetIdentifier::fromString('brand_intel_a1677570-a278-444b-ab46-baa1db199392');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($createAssetCommand->assetFamilyIdentifier);
        $labelAttributeReference = AttributeAsLabelReference::createFromNormalized('label_brand_fingerprint');

        $findAttributeAsLabel
            ->find(Argument::type(AssetFamilyIdentifier::class))
            ->willReturn($labelAttributeReference);

        $assetRepository->nextIdentifier(
            Argument::type(AssetFamilyIdentifier::class),
            Argument::type(AssetCode::class)
        )->willReturn($assetIdentifier);

        $assetRepository->create(Argument::that(function ($asset) use (
            $assetIdentifier,
            $assetFamilyIdentifier
        ) {
            Assert::count($asset->getRecordedEvents(), 1);
            Assert::isInstanceOf(current($asset->getRecordedEvents()), AssetCreatedEvent::class);
            $asset->clearRecordedEvents();

            $expectedAsset = Asset::fromState(
                $assetIdentifier,
                $assetFamilyIdentifier,
                AssetCode::fromString('intel'),
                ValueCollection::fromValues([]),
                $asset->getCreatedAt(),
                $asset->getUpdatedAt(),
            );

            Assert::eq($expectedAsset, $asset);
            return true;
        }))->shouldBeCalled();

        $this->__invoke($createAssetCommand);
    }
}
