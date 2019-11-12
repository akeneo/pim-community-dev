<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Factory;

use Akeneo\Pim\Enrichment\Component\Product\Factory\NonExistentValuesFilter\ChainedNonExistentValuesFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\BooleanValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\IdentifierValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\NumberValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\OptionValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\TextAreaValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\Value\TextValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Factory\ReadValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use PhpSpec\ObjectBehavior;

class ReadValueCollectionFactorySpec extends ObjectBehavior
{
    function let(
        GetAttributes $getAttributeByCodes,
        ChainedNonExistentValuesFilterInterface $chainedObsoleteValueFilter
    ) {
        $valueFactory = new ValueFactory(
            [
                new OptionValueFactory(),
                new BooleanValueFactory(),
                new NumberValueFactory(),
                new IdentifierValueFactory(),
                new TextAreaValueFactory(),
                new TextValueFactory(),
            ]
        );

        $this->beConstructedWith(
            $valueFactory,
            $getAttributeByCodes,
            $chainedObsoleteValueFilter
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ReadValueCollectionFactory::class);
    }

    function it_creates_a_values_collection_from_the_storage_format_for_one_entity(
        GetAttributes $getAttributeByCodes,
        ChainedNonExistentValuesFilterInterface $chainedObsoleteValueFilter
    ) {
        $sku = new Attribute('sku', AttributeTypes::IDENTIFIER, [], false, false, null, false, 'text', []);
        $description = new Attribute('description', AttributeTypes::TEXTAREA, [], true, true, null, false, 'textarea', []);

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

        $getAttributeByCodes->forCodes(['sku', 'description'])->willReturn(['sku' => $sku, 'description' => $description]);

        $chainedObsoleteValueFilter->filterAll(['not_used_identifier' => $rawValues])->willReturn(['not_used_identifier' => $rawValues]);

        $actualValues = $this->createFromStorageFormat($rawValues);

        $actualValues->shouldReturnAnInstanceOf(ReadValueCollection::class);
        $actualValues->shouldBeLike(new ReadValueCollection(
            [
                ScalarValue::value('sku', 'foo'),
                ScalarValue::scopableLocalizableValue('description', 'a text area for ecommerce in English', 'ecommerce', 'en_US'),
                ScalarValue::scopableLocalizableValue('description', 'a text area for tablets in English', 'tablet', 'en_US'),
                ScalarValue::scopableLocalizableValue('description', 'une zone de texte pour les tablettes en français', 'tablet', 'fr_FR'),
            ]
        ));
    }
}
