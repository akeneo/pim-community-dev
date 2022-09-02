<?php

declare(strict_types=1);

namespace spec\Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules;

use Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules\GetAssetCollectionTypeAdapterInterface;
use Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules\ProductAttributeCannotContainAssetsException;
use Akeneo\AssetManager\Infrastructure\Validation\AssetFamily\ProductLinkRules\ProductAttributeDoesNotExistException;
use Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetCollectionType;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AbstractAttribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class GetAssetCollectionTypeAdapterSpec extends ObjectBehavior
{
    function let(GetAttributes $getAttributes)
    {
        $this->beConstructedWith($getAttributes);
    }

    function it_is_initializable()
    {
        $this->shouldBeAnInstanceOf(GetAssetCollectionTypeAdapterInterface::class);
    }

    function it_fetches_the_asset_family_type_of_an_asset_collection_attribute(
        GetAttributes $getAttributes
    ){
        $getAttributes->forCode('my_asset_collection')->willReturn(
            new Attribute(
                'my_asset_collection',
                AssetCollectionType::ASSET_COLLECTION,
                ['reference_data_name' => 'my_asset_family_identifier'],
                false,
                false,
                null,
                null,
                false,
                AttributeTypes::BACKEND_TYPE_COLLECTION,
                []
            ),
        );

        $this->fetch('my_asset_collection')->shouldReturn('my_asset_family_identifier');
    }

    function it_throws_if_the_given_code_does_not_exist(
        GetAttributes $getAttributes
    ){
        $unknownAttributeCode = 'UNKNOWN_ASSET_COLLECTION_ATTRIBUTE';
        $getAttributes->forCode($unknownAttributeCode)->willReturn(null);

        $this->shouldThrow(
            new ProductAttributeDoesNotExistException(sprintf('Expected attribute "%s" to exist, none found', $unknownAttributeCode))
        )->during('fetch', [$unknownAttributeCode]);
    }

    function it_throws_if_the_given_code_does_not_reference_an_asset_collection_attribute(
        GetAttributes $getAttributes
    ){
        $productAttributeCode = 'my_asset_collection';
        $expectedAssetFamilyIdentifier = AssetCollectionType::ASSET_COLLECTION;
        $wrongAttributeType = 'NOT_AN_ASSET_COLLECTION_ATTRIBUTE';

        $getAttributes->forCode($productAttributeCode)->willReturn(
            new Attribute(
                $productAttributeCode,
                $wrongAttributeType,
                ['reference_data_name' => 'my_asset_family_identifier'],
                false,
                false,
                null,
                null,
                false,
                AttributeTypes::BACKEND_TYPE_COLLECTION,
                []
            ),
        );

        $this->shouldThrow(
            new ProductAttributeCannotContainAssetsException(
                sprintf('Expected attribute "%s" to be of type "%s", "%s" given',
                    $productAttributeCode,
                    $expectedAssetFamilyIdentifier,
                    $wrongAttributeType
                )
            )
        )->during('fetch', [$productAttributeCode]);
    }
}
