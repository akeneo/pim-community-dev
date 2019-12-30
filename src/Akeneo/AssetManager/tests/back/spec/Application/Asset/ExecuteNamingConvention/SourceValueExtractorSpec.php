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

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueDataInterface;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConvention;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\Source;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use PhpSpec\ObjectBehavior;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class SourceValueExtractorSpec extends ObjectBehavior
{
    function it_returns_the_asset_code_if_naming_convention_is_based_on_the_code(
        Asset $asset,
        NamingConvention $namingConvention,
        Source $source
    ) {
        $namingConvention->getSource()->willReturn($source);
        $source->isAssetCode()->willReturn(true);
        $asset->getCode()->willReturn(AssetCode::fromString('the_code'));

        $this->extract($asset, $namingConvention)->shouldReturn('the_code');
    }

    function it_returns_the_asset_data_value_if_naming_convention_is_based_a_valid_value(
        Asset $asset,
        NamingConvention $namingConvention,
        Source $source,
        Value $value,
        ValueDataInterface $valueData
    ) {
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

        $this->extract($asset, $namingConvention)->shouldReturn('normalized_value');
    }

    function it_returns_the_original_filename_if_naming_convention_is_based_on_a_file_value(
        Asset $asset,
        NamingConvention $namingConvention,
        Source $source,
        Value $value,
        FileData $fileData
    ) {
        $namingConvention->getSource()->willReturn($source);
        $source->isAssetCode()->willReturn(false);

        $source->getProperty()->willReturn('property');
        $channelReference = ChannelReference::noReference();
        $localeReference = LocaleReference::noReference();
        $source->getChannelReference()->willReturn($channelReference);
        $source->getLocaleReference()->willReturn($localeReference);
        $valueKey = ValueKey::create(AttributeIdentifier::fromString('property'), $channelReference, $localeReference);
        $asset->findValue($valueKey)->willReturn($value);

        $file = new FileInfo();
        $file->setKey('/a/file/key');
        $file->setOriginalFilename('my_file.png');
        $file->setSize(1024);
        $file->setMimeType('image/png');
        $file->setExtension('png');
        $fileData = FileData::createFromFileinfo($file, new \Datetime());
        $value->getData()->willReturn($fileData);

        $this->extract($asset, $namingConvention)->shouldReturn('my_file.png');
    }

    function it_returns_null_when_naming_convention_is_based_on_unknown_value(
        Asset $asset,
        NamingConvention $namingConvention,
        Source $source
    ) {
        $namingConvention->getSource()->willReturn($source);
        $source->isAssetCode()->willReturn(false);

        $source->getProperty()->willReturn('property');
        $channelReference = ChannelReference::noReference();
        $localeReference = LocaleReference::noReference();
        $source->getChannelReference()->willReturn($channelReference);
        $source->getLocaleReference()->willReturn($localeReference);
        $valueKey = ValueKey::create(AttributeIdentifier::fromString('property'), $channelReference, $localeReference);
        $asset->findValue($valueKey)->willReturn(null);

        $this->extract($asset, $namingConvention)->shouldReturn(null);
    }
}
