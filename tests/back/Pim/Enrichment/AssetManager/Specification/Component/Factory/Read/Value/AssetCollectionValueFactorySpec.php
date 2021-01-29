<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\AssetManager\Component\Factory\Read\Value;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetCollectionType;
use Akeneo\Pim\Enrichment\AssetManager\Component\Value\AssetCollectionValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use PhpSpec\ObjectBehavior;

class AssetCollectionValueFactorySpec extends ObjectBehavior
{
    function it_creates_an_asset_collection_value_checking_the_data()
    {
        $assetCollectionValue = $this->createByCheckingData(
            $this->buildAttribute('attribute_code'),
            null,
            null,
            ['code1', 'code2']
        );

        $assetCollectionValue->shouldBeAnInstanceOf(AssetCollectionValue::class);
        $assetCollectionValue->getAttributeCode()->shouldBe('attribute_code');
        $assetCollectionValue->getData()->shouldHaveCount(2);
        $assetCollectionValue->getData()[0]->shouldBeAnInstanceOf(AssetCode::class);
        $assetCollectionValue->getData()[0]->normalize()->shouldBe('code1');
        $assetCollectionValue->getData()[1]->shouldBeAnInstanceOf(AssetCode::class);
        $assetCollectionValue->getData()[1]->normalize()->shouldBe('code2');
    }

    function it_throws_an_error_when_one_of_values_is_not_a_atring()
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)->during('createByCheckingData', [
            $this->buildAttribute('attribute_code'),
            null,
            null,
            ['code1', []]
        ]);
    }

    function it_throws_an_error_when_one_of_values_does_not_satisfy_the_code_business_rules()
    {
        $this->shouldThrow(InvalidPropertyException::class)->during('createByCheckingData', [
            $this->buildAttribute('attribute_code'),
            null,
            null,
            ['code1', 'code 2']
        ]);
    }

    private function buildAttribute(string $code): Attribute
    {
        return new Attribute(
            $code,
            AssetCollectionType::ASSET_COLLECTION,
            [],
            false,
            false,
            null,
            null,
            null,
            'asset_collection',
            []
        );
    }
}
