<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Persistence\Cache\AssetAttribute;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributesIndexedByIdentifierInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Cache\AssetAttribute\LRUCachedFindAttributesIndexedByIdentifier;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LRUCachedFindAttributesIndexedByIdentifierSpec
{
    function let(FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier)
    {
        $this->beConstructedWith($findAttributesIndexedByIdentifier);
    }

    function it_is_initializable()
    {
        $this->shouldImplement(FindAttributesIndexedByIdentifierInterface::class);
        $this->shouldHaveType(LRUCachedFindAttributesIndexedByIdentifier::class);
    }

    function it_caches_asset_attributes_depend_on_asset_family(
        FindAttributesIndexedByIdentifierInterface $findAttributesIndexedByIdentifier,
        AssetFamilyIdentifier $anAssetFamilyIdentifier
    ) {
        $findAttributesIndexedByIdentifier->find($anAssetFamilyIdentifier)->shouldBeCalledOnce();

        $severalCallsToFindAssetAttributes = 3;
        for ($i = 0; $i < $severalCallsToFindAssetAttributes; ++$i) {
            $this->find($anAssetFamilyIdentifier);
        }
    }
}
