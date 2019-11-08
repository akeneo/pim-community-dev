<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Factory\ReadValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\WriteValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use PhpSpec\ObjectBehavior;

class WriteValueCollectionFactorySpec extends ObjectBehavior
{
    function let(ReadValueCollectionFactory $readValueCollectionFactory) {
        $this->beConstructedWith($readValueCollectionFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(WriteValueCollectionFactory::class);
    }

    function it_creates_a_values_collection_from_the_storage_format_for_single_entity(
        ReadValueCollectionFactory $readValueCollectionFactory
    ) {
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

        $readValueCollectionFactory->createFromStorageFormat($rawValues)->willReturn(new ReadValueCollection());
        $this->createFromStorageFormat($rawValues)->shouldBeLike(new WriteValueCollection());
    }

    function it_creates_a_values_collection_from_the_storage_format_for_several_entities(
        ReadValueCollectionFactory $readValueCollectionFactory
    ) {
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

        $readValueCollectionFactory->createMultipleFromStorageFormat($rawValues)->willReturn(['product' => new ReadValueCollection()]);
        $this->createMultipleFromStorageFormat($rawValues)->shouldBeLike(['product' => new WriteValueCollection()]);
    }
}
