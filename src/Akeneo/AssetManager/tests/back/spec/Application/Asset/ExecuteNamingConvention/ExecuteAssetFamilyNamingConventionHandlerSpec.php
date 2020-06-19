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

use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\ExecuteAssetFamilyNamingConventionHandler;
use Akeneo\AssetManager\Application\Asset\ExecuteNamingConvention\NamingConventionLauncherInterface;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConventionInterface;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use PhpSpec\ObjectBehavior;

class ExecuteAssetFamilyNamingConventionHandlerSpec extends ObjectBehavior
{
    function let(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        NamingConventionLauncherInterface $namingConventionLauncher
    ) {
        $this->beConstructedWith(
            $assetFamilyRepository,
            $namingConventionLauncher
        );
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(ExecuteAssetFamilyNamingConventionHandler::class);
    }

    function is_does_nothing_if_the_naming_convention_is_empty(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        NamingConventionLauncherInterface $namingConventionLauncher,
        AssetFamily $assetFamily,
        NamingConventionInterface $namingConvention
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('family');
        $assetFamilyRepository->getByIdentifier($assetFamilyIdentifier)->willReturn($assetFamily);
        $assetFamily->getNamingConvention()->willReturn($namingConvention);
        $namingConvention->isEmpty()->willReturn(true);

        $namingConventionLauncher
            ->launchForAllAssetFamilyAssets($assetFamilyIdentifier)
            ->shouldNotBeCalled();

        $this->__invoke();
    }

    function is_launch_a_job_if_there_is_a_naming_convention(
        AssetFamilyRepositoryInterface $assetFamilyRepository,
        NamingConventionLauncherInterface $namingConventionLauncher,
        AssetFamily $assetFamily,
        NamingConventionInterface $namingConvention
    ) {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('family');
        $assetFamilyRepository->getByIdentifier($assetFamilyIdentifier)->willReturn($assetFamily);
        $assetFamily->getNamingConvention()->willReturn($namingConvention);
        $namingConvention->isEmpty()->willReturn(false);

        $namingConventionLauncher
            ->launchForAllAssetFamilyAssets($assetFamilyIdentifier)
            ->shouldBeCalled();

        $this->__invoke();
    }
}
