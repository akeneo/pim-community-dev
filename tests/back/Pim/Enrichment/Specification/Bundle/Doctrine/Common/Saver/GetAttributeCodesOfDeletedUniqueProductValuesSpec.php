<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\Common\Saver\GetAttributeCodesOfDeletedUniqueProductValues;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Test\Common\EntityBuilder;
use Akeneo\Test\Common\Structure\Attribute\Builder;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;

class GetAttributeCodesOfDeletedUniqueProductValuesSpec extends ObjectBehavior
{
    function let(IdentifiableObjectRepositoryInterface $attributeRepository)
    {
        $uniqueNumberAttribute = (new Builder())->withCode('123')->aUniqueAttribute()->build();

        $uniqueNameAttribute = (new Builder())->withCode('name')->aUniqueAttribute()->build();
        $descriptionAttribute = (new Builder())->withCode('description')->aTextAttribute()->build();
        $titleAttribute = (new Builder())->withCode('title')->aTextAttribute()->build();

        $attributeRepository->findOneByIdentifier('123')->willReturn($uniqueNumberAttribute);
        $attributeRepository->findOneByIdentifier('name')->willReturn($uniqueNameAttribute);
        $attributeRepository->findOneByIdentifier('description')->willReturn($descriptionAttribute);
        $attributeRepository->findOneByIdentifier('title')->willReturn($titleAttribute);

        $this->beConstructedWith($attributeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(GetAttributeCodesOfDeletedUniqueProductValues::class);
    }

    function it_returns_attribute_codes_of_deleted_unique_values()
    {
        $product = new Product();
        $product->setRawValues([
            'title' => [
                '<all_channels>' => [
                    '<all_locales>' => 'my_title'
                ]
            ],
            'description' => [
                '<all_channels>' => [
                    '<all_locales>' => 'desc'
                ]
            ],
            'name' => [
                '<all_channels>' => [
                    '<all_locales>' => 'unique_data'
                ]
            ]
        ]);

        $product->setValues(new ValueCollection([
            ScalarValue::value('description', 'new_desc'),
        ]));

        $this->compute($product)->shouldReturn(['name']);
    }

    function it_returns_attribute_codes_of_deleted_unique_value_for_a_numeric_attribute_code()
    {
        $product = new Product();
        $product->setRawValues([
            'title' => [
                '<all_channels>' => [
                    '<all_locales>' => 'my_title'
                ]
            ],
            'description' => [
                '<all_channels>' => [
                    '<all_locales>' => 'desc'
                ]
            ],
            '123' => [
                '<all_channels>' => [
                    '<all_locales>' => 'unique_data'
                ]
            ]
        ]);

        $product->setValues(new ValueCollection([
            ScalarValue::value('description', 'new_desc'),
        ]));

        $this->compute($product)->shouldReturn(['123']);
    }

    function it_returns_empty_array_when_no_deleted_value_for_a_unique_attribute()
    {
        $product = new Product();
        $product->setRawValues([
            'title' => [
                '<all_channels>' => [
                    '<all_locales>' => 'my_title'
                ]
            ],
            'description' => [
                '<all_channels>' => [
                    '<all_locales>' => 'desc'
                ]
            ]
        ]);

        $product->setValues(new ValueCollection([
            ScalarValue::value('description', 'new_desc'),
        ]));

        $this->compute($product)->shouldReturn([]);
    }

    function it_returns_empty_array_when_no_deleted_value_for_a_unique_numeric_attribute()
    {
        $product = new Product();
        $product->setRawValues([
            '123' => [
                '<all_channels>' => [
                    '<all_locales>' => 'unique_data'
                ]
            ]
        ]);

        $product->setValues(new ValueCollection([
            ScalarValue::value('123', 'unique_data'),
        ]));

        $this->compute($product)->shouldReturn([]);
    }

    function it_returns_empty_array_when_the_value_for_a_unique_attribute_is_only_updated()
    {
        $product = new Product();
        $product->setRawValues([
            'title' => [
                '<all_channels>' => [
                    '<all_locales>' => 'my_title'
                ]
            ],
            'description' => [
                '<all_channels>' => [
                    '<all_locales>' => 'desc'
                ]
            ],
            'name' => [
                '<all_channels>' => [
                    '<all_locales>' => 'unique_data'
                ]
            ]
        ]);

        $product->setValues(new ValueCollection([
            ScalarValue::value('name', 'new_desc'),
        ]));

        $this->compute($product)->shouldReturn([]);
    }
}
