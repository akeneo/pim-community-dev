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
use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\SourceValueExtractor;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConvention;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NullNamingConvention;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ExecuteNamingConventionHandlerSpec extends ObjectBehavior
{
    function let(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AssetRepositoryInterface $assetRepository,
        SourceValueExtractor $sourceValueExtractor
    ) {
        $this->beConstructedWith($assetFamilyRepository, $assetRepository, $sourceValueExtractor);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ExecuteNamingConventionHandler::class);
    }

    function it_executes_a_naming_convention_when_source_value_can_be_extracted(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AssetRepositoryInterface $assetRepository,
        SourceValueExtractor $sourceValueExtractor,
        AssetFamily $assetFamily,
        Asset $asset,
        NamingConvention $namingConvention
    ) {
        $assetCode = AssetCode::fromString('code');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('family');
        $assetFamilyRepository->getByIdentifier($assetFamilyIdentifier)->willReturn($assetFamily);
        $assetRepository->getByAssetFamilyAndCode($assetFamilyIdentifier, $assetCode)->willReturn($asset);

        $assetFamily->getNamingConvention()->willReturn($namingConvention);

        $sourceValueExtractor->extract($asset, $namingConvention)->willReturn('the_code');

        $executeNamingConventionCommand = new ExecuteNamingConventionCommand($assetCode, $assetFamilyIdentifier);

        // @todo: add later the fact that something is triggered

        $this->__invoke($executeNamingConventionCommand);
    }

    function it_does_nothing_with_a_null_naming_convention(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AssetFamily $assetFamily,
        NullNamingConvention $nullNamingConvention
    ) {
        $assetCode = AssetCode::fromString('code');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('family');
        $assetFamilyRepository->getByIdentifier($assetFamilyIdentifier)->willReturn($assetFamily);

        $assetFamily->getNamingConvention()->willReturn($nullNamingConvention);

        $executeNamingConventionCommand = new ExecuteNamingConventionCommand($assetCode, $assetFamilyIdentifier);

        // @todo: add later the fact that nothing is triggered

        $this->__invoke($executeNamingConventionCommand);
    }

    function it_does_nothing_when_source_value_can_not_be_extracted(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        AssetRepositoryInterface $assetRepository,
        SourceValueExtractor $sourceValueExtractor,
        AssetFamily $assetFamily,
        Asset $asset,
        ExecuteNamingConventionCommand $executeNamingConventionCommand,
        NamingConvention $namingConvention
    ) {
        $assetCode = AssetCode::fromString('code');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('family');
        $assetFamilyRepository->getByIdentifier($assetFamilyIdentifier)->willReturn($assetFamily);
        $assetRepository->getByAssetFamilyAndCode($assetFamilyIdentifier, $assetCode)->willReturn($asset);

        $assetFamily->getNamingConvention()->willReturn($namingConvention);
        $sourceValueExtractor->extract($asset, $namingConvention)->willReturn(null);

        $executeNamingConventionCommand = new ExecuteNamingConventionCommand($assetCode, $assetFamilyIdentifier);

        // @todo: add later the fact that nothing is triggered

        $this->__invoke($executeNamingConventionCommand);
    }
}
