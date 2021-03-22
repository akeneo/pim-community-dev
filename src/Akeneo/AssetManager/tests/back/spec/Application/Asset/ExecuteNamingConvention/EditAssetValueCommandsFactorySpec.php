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

namespace spec\Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditMediaLinkValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditNumberValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditTextValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditValueCommandFactoryInterface;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditValueCommandFactoryRegistryInterface;
use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\EditAssetValueCommandsFactory;
use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\Exception\NamingConventionPatternNotMatch;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConvention;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\Pattern;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeDecimalsAllowed;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeLimit;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeMaxLength;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeRegularExpression;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValidationRule;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\MediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Prefix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Suffix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\NumberAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\TextAttribute;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeNotFoundException;
use PhpSpec\ObjectBehavior;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
class EditAssetValueCommandsFactorySpec extends ObjectBehavior
{
    function let(
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier,
        EditValueCommandFactoryRegistryInterface $editValueCommandFactoryRegistry
    ) {
        $this->beConstructedWith($findAttributesIndexedByIdentifier, $editValueCommandFactoryRegistry);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(EditAssetValueCommandsFactory::class);
    }

    function it_returns_a_list_of_edit_asset_value_commands(
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier,
        EditValueCommandFactoryRegistryInterface $editValueCommandFactoryRegistry,
        NamingConvention $namingConvention,
        EditValueCommandFactoryInterface $editValueCommandFactory,
        EditTextValueCommand $editTextValueCommand,
        EditNumberValueCommand $editNumberValueCommand,
        EditMediaLinkValueCommand $editMediaLinkValueCommand
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('family');
        $sourceValue = 'the title_12_the_link-useless part';

        $namingConvention->abortAssetCreationOnError()->willReturn(false);
        $namingConvention->getPattern()
            ->willReturn(Pattern::create('/(?P<title>[a-zA-Z0-9\s]+)_(?P<length>\d+)_(?P<link>\w+)/'));

        $titleAttribute = $this->getTextAttribute('title');
        $lengthAttribute = $this->getNumberAttribute('length');
        $linkAttribute = $this->getMediaLinkAttribute('link');
        $findAttributesIndexedByIdentifier->find($assetFamilyIdentifier)->willReturn([
            $titleAttribute,
            $this->getTextAttribute('other_title'),
            $lengthAttribute,
            $linkAttribute,
        ]);

        $normalizedValue = [
            'data' => 'the title',
            'channel' => null,
            'locale' => null,
        ];
        $editValueCommandFactoryRegistry->getFactory($titleAttribute, $normalizedValue)->willReturn($editValueCommandFactory);
        $editValueCommandFactory->create($titleAttribute, $normalizedValue)->willReturn($editTextValueCommand);
        $normalizedValue = [
            'data' => '12',
            'channel' => null,
            'locale' => null,
        ];
        $editValueCommandFactoryRegistry->getFactory($lengthAttribute, $normalizedValue)->willReturn($editValueCommandFactory);
        $editValueCommandFactory->create($lengthAttribute, $normalizedValue)->willReturn($editNumberValueCommand);
        $normalizedValue = [
            'data' => 'the_link',
            'channel' => null,
            'locale' => null,
        ];
        $editValueCommandFactoryRegistry->getFactory($linkAttribute, $normalizedValue)->willReturn($editValueCommandFactory);
        $editValueCommandFactory->create($linkAttribute, $normalizedValue)->willReturn($editMediaLinkValueCommand);

        $this->create($assetFamilyIdentifier, $namingConvention, $sourceValue)
            ->shouldBe([$editTextValueCommand, $editNumberValueCommand, $editMediaLinkValueCommand]);
    }

    function it_returns_an_empty_list_if_string_dont_match_in_non_strict_mode(NamingConvention $namingConvention)
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('family');
        $sourceValue = 'dont_match';

        $namingConvention->abortAssetCreationOnError()->willReturn(false);
        $namingConvention->getPattern()
            ->willReturn(Pattern::create('/(?P<title>[a-zA-Z0-9\s]+)_(?P<length>\d+)_(?P<link>\w+)/'));

        $this->create($assetFamilyIdentifier, $namingConvention, $sourceValue)->shouldBe([]);
    }

    function it_throws_an_exception_if_string_dont_match_in_strict_mode(NamingConvention $namingConvention)
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('family');
        $sourceValue = 'dont_match';

        $namingConvention->abortAssetCreationOnError()->willReturn(true);
        $namingConvention->getPattern()
            ->willReturn(Pattern::create('/(?P<title>[a-zA-Z0-9\s]+)_(?P<length>\d+)_(?P<link>\w+)/'));

        $this->shouldThrow(NamingConventionPatternNotMatch::class)
            ->during('create', [$assetFamilyIdentifier, $namingConvention, $sourceValue]);
    }

    function it_returns_a_list_of_edit_asset_value_commands_only_for_known_attribute_in_non_strict_mode(
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier,
        EditValueCommandFactoryRegistryInterface $editValueCommandFactoryRegistry,
        NamingConvention $namingConvention,
        EditValueCommandFactoryInterface $editValueCommandFactory,
        EditTextValueCommand $editTextValueCommand,
        EditMediaLinkValueCommand $editMediaLinkValueCommand
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('family');
        $sourceValue = 'the title_12_the_link-useless part';

        $namingConvention->abortAssetCreationOnError()->willReturn(false);
        $namingConvention->getPattern()
            ->willReturn(Pattern::create('/(?P<title>[a-zA-Z0-9\s]+)_(?P<length>\d+)_(?P<link>\w+)/'));

        $titleAttribute = $this->getTextAttribute('title');
        $linkAttribute = $this->getMediaLinkAttribute('link');
        $findAttributesIndexedByIdentifier->find($assetFamilyIdentifier)->willReturn([
            $titleAttribute,
            $this->getTextAttribute('other_title'),
            $linkAttribute,
        ]);

        $normalizedValue = [
            'data' => 'the title',
            'channel' => null,
            'locale' => null,
        ];
        $editValueCommandFactoryRegistry->getFactory($titleAttribute, $normalizedValue)->willReturn($editValueCommandFactory);
        $editValueCommandFactory->create($titleAttribute, $normalizedValue)->willReturn($editTextValueCommand);

        $normalizedValue = [
            'data' => 'the_link',
            'channel' => null,
            'locale' => null,
        ];
        $editValueCommandFactoryRegistry->getFactory($linkAttribute, $normalizedValue)->willReturn($editValueCommandFactory);
        $editValueCommandFactory->create($linkAttribute, $normalizedValue)->willReturn($editMediaLinkValueCommand);

        $this->create($assetFamilyIdentifier, $namingConvention, $sourceValue)
            ->shouldBe([$editTextValueCommand, $editMediaLinkValueCommand]);
    }

    function it_throws_an_exception_when_a_target_attribute_is_unknown_in_strict_mode(
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier,
        EditValueCommandFactoryRegistryInterface $editValueCommandFactoryRegistry,
        NamingConvention $namingConvention,
        EditValueCommandFactoryInterface $editValueCommandFactory,
        EditTextValueCommand $editTextValueCommand,
        EditMediaLinkValueCommand $editMediaLinkValueCommand
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('family');
        $sourceValue = 'the title_12_the_link-useless part';

        $namingConvention->abortAssetCreationOnError()->willReturn(true);
        $namingConvention->getPattern()
            ->willReturn(Pattern::create('/(?P<title>[a-zA-Z0-9\s]+)_(?P<length>\d+)_(?P<link>\w+)/'));

        $titleAttribute = $this->getTextAttribute('title');
        $linkAttribute = $this->getMediaLinkAttribute('link');
        $findAttributesIndexedByIdentifier->find($assetFamilyIdentifier)->willReturn([
            $titleAttribute,
            $this->getTextAttribute('other_title'),
            $linkAttribute,
        ]);

        $normalizedValue = [
            'data' => 'the title',
            'channel' => null,
            'locale' => null,
        ];
        $editValueCommandFactoryRegistry->getFactory($titleAttribute, $normalizedValue)->willReturn($editValueCommandFactory);
        $editValueCommandFactory->create($titleAttribute, $normalizedValue)->willReturn($editTextValueCommand);

        $this->shouldThrow(AttributeNotFoundException::class)
            ->during('create', [$assetFamilyIdentifier, $namingConvention, $sourceValue]);
    }

    private function getTextAttribute(string $code): TextAttribute
    {
        return TextAttribute::createText(
            AttributeIdentifier::fromString(uniqid()),
            AssetFamilyIdentifier::fromString('asset_family_identifier'),
            AttributeCode::fromString($code),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeMaxLength::noLimit(),
            AttributeValidationRule::fromString('none'),
            AttributeRegularExpression::createEmpty()
        );
    }

    private function getNumberAttribute(string $code): NumberAttribute
    {
        return NumberAttribute::create(
            AttributeIdentifier::fromString(uniqid()),
            AssetFamilyIdentifier::fromString('asset_family_identifier'),
            AttributeCode::fromString($code),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AttributeDecimalsAllowed::fromBoolean(false),
            AttributeLimit::limitless(),
            AttributeLimit::limitless()
        );
    }

    private function getMediaLinkAttribute(string $code): MediaLinkAttribute
    {
        return MediaLinkAttribute::create(
            AttributeIdentifier::fromString(uniqid()),
            AssetFamilyIdentifier::fromString('asset_family_identifier'),
            AttributeCode::fromString($code),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(1),
            AttributeIsRequired::fromBoolean(false),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            Prefix::createEmpty(),
            Suffix::createEmpty(),
            MediaType::fromString(MediaType::OTHER)
        );
    }
}
