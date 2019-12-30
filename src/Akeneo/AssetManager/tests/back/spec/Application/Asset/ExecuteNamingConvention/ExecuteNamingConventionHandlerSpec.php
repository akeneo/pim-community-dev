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

use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\ExecuteNamingConventionCommand;
use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\ExecuteNamingConventionHandler;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueDataInterface;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConvention;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NullNamingConvention;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\Source;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ExecuteNamingConventionHandlerSpec extends ObjectBehavior
{
    function let(AssetFamilyRepositoryInterface $assetFamilyRepository, AssetRepositoryInterface $assetRepository)
    {
        $this->beConstructedWith($assetFamilyRepository, $assetRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ExecuteNamingConventionHandler::class);
    }

    function it_executes_a_naming_convention_when_source_is_the_code(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AssetRepositoryInterface $assetRepository,
        AssetFamily $assetFamily,
        Asset $asset,
        ExecuteNamingConventionCommand $executeNamingConventionCommand,
        NamingConvention $namingConvention,
        Source $source
    ) {
        $assetCode = AssetCode::fromString('code');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('family');
        $assetFamilyRepository->getByIdentifier($assetFamilyIdentifier)->willReturn($assetFamily);
        $assetRepository->getByAssetFamilyAndCode($assetFamilyIdentifier, $assetCode)->willReturn($asset);

        $assetFamily->getNamingConvention()->willReturn($namingConvention);
        $namingConvention->getSource()->willReturn($source);
        $source->isAssetCode()->willReturn(true);

        $asset->getCode()->willReturn(AssetCode::fromString('the_code'));

        $executeNamingConventionCommand->assetCode = $assetCode;
        $executeNamingConventionCommand->assetFamilyIdentifier = $assetFamilyIdentifier;

        // @todo: add later the fact that something is triggered

        $this->__invoke($executeNamingConventionCommand);
    }

    function it_executes_a_naming_convention_when_source_is_a_value(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AssetRepositoryInterface $assetRepository,
        AssetFamily $assetFamily,
        Asset $asset,
        ExecuteNamingConventionCommand $executeNamingConventionCommand,
        NamingConvention $namingConvention,
        Source $source,
        Value $value,
        ValueDataInterface $valueData
    ) {
        $assetCode = AssetCode::fromString('code');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('family');
        $assetFamilyRepository->getByIdentifier($assetFamilyIdentifier)->willReturn($assetFamily);
        $assetRepository->getByAssetFamilyAndCode($assetFamilyIdentifier, $assetCode)->willReturn($asset);

        $assetFamily->getNamingConvention()->willReturn($namingConvention);
        $namingConvention->getSource()->willReturn($source);
        $source->isAssetCode()->willReturn(false);

        $source->getProperty()->willReturn('property');
        $channelReference = ChannelReference::noReference();
        $localeReference = LocaleReference::noReference();
        $source->getChannelReference()->willReturn($channelReference);
        $source->getLocaleReference()->willReturn($localeReference);
        $valueKey = ValueKey::create(AttributeIdentifier::fromString('property'), $channelReference, $localeReference);
        $asset->findValue($valueKey)->willReturn($value);

        $value->getData()->willReturn($valueData);
        $valueData->normalize()->willReturn('normalized_value');

        $executeNamingConventionCommand->assetCode = $assetCode;
        $executeNamingConventionCommand->assetFamilyIdentifier = $assetFamilyIdentifier;

        // @todo: add later the fact that something is triggered

        $this->__invoke($executeNamingConventionCommand);
    }

    function it_does_nothing_with_a_null_naming_convention(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AssetFamily $assetFamily,
        Asset $asset,
        ExecuteNamingConventionCommand $executeNamingConventionCommand,
        NullNamingConvention $nullNamingConvention
    ) {
        $assetCode = AssetCode::fromString('code');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('family');
        $assetFamilyRepository->getByIdentifier($assetFamilyIdentifier)->willReturn($assetFamily);

        $assetFamily->getNamingConvention()->willReturn($nullNamingConvention);

        $executeNamingConventionCommand->assetCode = $assetCode;
        $executeNamingConventionCommand->assetFamilyIdentifier = $assetFamilyIdentifier;

        // @todo: add later the fact that nothing is triggered

        $this->__invoke($executeNamingConventionCommand);
    }

    function it_does_nothing_when_source_is_not_found(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AssetRepositoryInterface $assetRepository,
        AssetFamily $assetFamily,
        Asset $asset,
        ExecuteNamingConventionCommand $executeNamingConventionCommand,
        NamingConvention $namingConvention,
        Source $source
    ) {
        $assetCode = AssetCode::fromString('code');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('family');
        $assetFamilyRepository->getByIdentifier($assetFamilyIdentifier)->willReturn($assetFamily);
        $assetRepository->getByAssetFamilyAndCode($assetFamilyIdentifier, $assetCode)->willReturn($asset);

        $assetFamily->getNamingConvention()->willReturn($namingConvention);
        $namingConvention->getSource()->willReturn($source);
        $source->isAssetCode()->willReturn(false);

        $source->getProperty()->willReturn('property');
        $channelReference = ChannelReference::noReference();
        $localeReference = LocaleReference::noReference();
        $source->getChannelReference()->willReturn($channelReference);
        $source->getLocaleReference()->willReturn($localeReference);
        $valueKey = ValueKey::create(AttributeIdentifier::fromString('property'), $channelReference, $localeReference);
        $asset->findValue($valueKey)->willReturn(null);

        $executeNamingConventionCommand->assetCode = $assetCode;
        $executeNamingConventionCommand->assetFamilyIdentifier = $assetFamilyIdentifier;

        // @todo: add later the fact that nothing is triggered

        $this->__invoke($executeNamingConventionCommand);
    }
}
