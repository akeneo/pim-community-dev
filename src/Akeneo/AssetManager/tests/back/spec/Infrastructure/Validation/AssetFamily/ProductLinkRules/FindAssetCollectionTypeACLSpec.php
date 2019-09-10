<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules;

use Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules\FindAssetCollectionTypeACLInterface;
use Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetMultipleLinkType;
use Akeneo\Pim\Structure\Component\Model\AbstractAttribute;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class FindAssetCollectionTypeACLSpec extends ObjectBehavior
{
    function let(AttributeRepositoryInterface $attributeRepository)
    {
        $this->beConstructedWith($attributeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(FindAssetCollectionTypeACLInterface::class);
    }

    function it_fetches_the_asset_family_type_of_an_asset_collection_attribute(
        AttributeRepositoryInterface $attributeRepository,
        AbstractAttribute $assetCollectionAttribute
    ){
        $productAttributeCode = 'my_asset_collection';
        $expectedAssetFamilyIdentifier = 'my_asset_family_identifier';
        $assetCollectionAttribute->getType()->willReturn(AssetMultipleLinkType::ASSET_MULTIPLE_LINK);
        $assetCollectionAttribute->getReferenceDataName()->willReturn($expectedAssetFamilyIdentifier);
        $attributeRepository->findOneByIdentifier($productAttributeCode)->willReturn($assetCollectionAttribute);

        $this->fetch($productAttributeCode)->shouldReturn($expectedAssetFamilyIdentifier);
    }

    function it_throws_if_the_given_code_does_not_reference_an_asset_collection_attribute(
        AttributeRepositoryInterface $attributeRepository,
        AbstractAttribute $assetCollectionAttribute
    ){
        $productAttributeCode = 'my_asset_collection';
        $expectedAssetFamilyIdentifier = AssetMultipleLinkType::ASSET_MULTIPLE_LINK;
        $wrongAttributeType = 'NOT_AN_ASSET_COLLECTION_ATTRIBUTE';
        $assetCollectionAttribute->getCode()->willReturn($productAttributeCode);
        $assetCollectionAttribute->getType()->willReturn($wrongAttributeType);
        $assetCollectionAttribute->getReferenceDataName()->willReturn($expectedAssetFamilyIdentifier);
        $attributeRepository->findOneByIdentifier($productAttributeCode)->willReturn($assetCollectionAttribute);

        $this->shouldThrow(
            new \InvalidArgumentException(
                sprintf('Expected attribute "%s" to be of type "%s", "%s" given',
                    $productAttributeCode,
                    $expectedAssetFamilyIdentifier,
                    $wrongAttributeType
                )
            )
        )->during('fetch', [$productAttributeCode]);
    }
}
