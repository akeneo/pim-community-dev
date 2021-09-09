<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Cache\AssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyExistsInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Cache\AssetFamily\LRUCachedAssetFamilyExists;
use PhpSpec\ObjectBehavior;

class LRUCachedAssetFamilyExistsSpec extends ObjectBehavior
{
    function let(AssetFamilyExistsInterface $assetFamilyExists)
    {
        $this->beConstructedWith($assetFamilyExists);
    }

    function it_is_a_query_to_determine_if_an_asset_family_exists()
    {
        $this->shouldImplement(AssetFamilyExistsInterface::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LRUCachedAssetFamilyExists::class);
    }

    function it_keeps_in_cache_if_an_asset_family_exists(AssetFamilyExistsInterface $assetFamilyExists)
    {
        $identifier = AssetFamilyIdentifier::fromString('designer');
        $assetFamilyExists->withIdentifier($identifier, true)
            ->shouldBeCalledOnce()
            ->willReturn(true);

        $this->withIdentifier($identifier)->shouldReturn(true);
        $this->withIdentifier($identifier)->shouldReturn(true);
    }

    function it_caches_separately_case_sensitive_asset_family_identifier(AssetFamilyExistsInterface $assetFamilyExists)
    {
        $lowercaseIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetFamilyExists->withIdentifier($lowercaseIdentifier, true)
            ->shouldBeCalledOnce()
            ->willReturn(true);

        $uppercaseIdentifier = AssetFamilyIdentifier::fromString('DESIGNER');
        $assetFamilyExists->withIdentifier($uppercaseIdentifier, true)
            ->shouldBeCalledOnce()
            ->willReturn(false);

        $this->withIdentifier($lowercaseIdentifier)->shouldReturn(true);
        $this->withIdentifier($lowercaseIdentifier)->shouldReturn(true);

        $this->withIdentifier($uppercaseIdentifier)->shouldReturn(false);
        $this->withIdentifier($uppercaseIdentifier)->shouldReturn(false);
    }
}
