<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\WriteValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use PhpSpec\ObjectBehavior;

class WriteValueCollectionFactorySpec extends ObjectBehavior
{
    function let(ValueCollectionFactory $valueCollectionFactory) {
        $this->beConstructedWith($valueCollectionFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(WriteValueCollectionFactory::class);
    }

    function it_creates_a_values_collection_from_the_storage_format_for_single_entity(ValueCollectionFactory $valueCollectionFactory) {
        $rawValues = [
            'sku' => [
                '<all_channels>' => [
                    '<all_locales>' => 'foo'
                ],
            ],
            'description' => [
                'ecommerce' => [
                    'en_US' => 'a text area for ecommerce in English',
                ],
                'tablet' => [
                    'en_US' => 'a text area for tablets in English',
                    'fr_FR' => 'une zone de texte pour les tablettes en français',
                ],
            ],
        ];

        $valueCollectionFactory->createFromStorageFormat($rawValues)->willReturn(new ReadValueCollection());
        $this->createFromStorageFormat($rawValues)->shouldBeLike(new WriteValueCollection());
    }

    function it_creates_a_values_collection_from_the_storage_format_for_several_entities(ValueCollectionFactory $valueCollectionFactory) {
        $rawValues = [
            'product' => [
                'sku' => [
                    '<all_channels>' => [
                        '<all_locales>' => 'foo'
                    ],
                ],
                'description' => [
                    'ecommerce' => [
                        'en_US' => 'a text area for ecommerce in English',
                    ],
                    'tablet' => [
                        'en_US' => 'a text area for tablets in English',
                        'fr_FR' => 'une zone de texte pour les tablettes en français',
                    ],
                ],
            ]
        ];

        $valueCollectionFactory->createMultipleFromStorageFormat($rawValues)->willReturn(['product' => new ReadValueCollection()]);
        $this->createMultipleFromStorageFormat($rawValues)->shouldBeLike(['product' => new WriteValueCollection()]);
    }
}
