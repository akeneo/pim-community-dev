<?php

namespace spec\Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\QueryGenerator;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\NamingUtility;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Pim\Component\Catalog\Model\AttributeOptionValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MultipleOptionValueUpdatedQueryGeneratorSpec extends ObjectBehavior
{
    function let(NamingUtility $namingUtility, NormalizerInterface $attributeOptionNormalizer)
    {
        $this->beConstructedWith($namingUtility, 'Pim\Bundle\CatalogBundle\Model\AttributeOptionValue', 'value', $attributeOptionNormalizer);
    }

    function it_generates_a_query_to_update_product_select_attributes(
        $namingUtility,
        AttributeOptionValueInterface $bleu,
        AttributeOptionInterface $blue,
        AttributeInterface $color,
        $attributeOptionNormalizer
    ) {
        $bleu->getOption()->willReturn($blue);
        $bleu->getLocale()->willReturn('fr_FR');
        $blue->getAttribute()->willReturn($color);
        $namingUtility
            ->getAttributeNormFields($color)
            ->willReturn(['normalizedData.color-fr_FR', 'normalizedData.color-en_US']);

        $blue->getCode()->willReturn('blue');
        $blue->getId()->willReturn('42');

        $attributeOptionNormalized = [
            'id' => 42,
            'code' => 'blue',
            'optionValues' => [
                'en_US' => [
                    'value' => 'Blue',
                    'locale' => 'en_US',
                ],
                'fr_FR' => [
                    'value' => 'Bleus',
                    'locale' => 'fr_FR',
                ],
            ]
        ];

        $attributeOptionNormalizer
            ->normalize($blue)
            ->willReturn($attributeOptionNormalized);

        $this->generateQuery($bleu, 'value', 'Bleu', 'Bleus')->shouldReturn([
            [
                [
                    '$and' => [
                        ['values.optionIds' => 42],
                        ['normalizedData.color-fr_FR' => ['$elemMatch' => ['code' => 'blue']]]
                    ]
                ],
                ['$push' => ['normalizedData.color-fr_FR' => $attributeOptionNormalized]],
                ['multiple' => true],
            ],
            [
                ['values.optionIds' => 42],
                [
                    '$pull' => [
                        'normalizedData.color-fr_FR' => [
                            '$and' => [
                                ['code' => 'blue'],
                                ['optionValues.fr_FR.value' => 'Bleu']
                            ]
                        ]
                    ]
                ],
                ['multiple' => true],
            ],
            [
                [
                    '$and' => [
                        ['values.optionIds' => 42],
                        ['normalizedData.color-en_US' => ['$elemMatch' => ['code' => 'blue']]]
                    ]
                ],
                ['$push' => ['normalizedData.color-en_US' => $attributeOptionNormalized]],
                ['multiple' => true],
            ],
            [
                ['values.optionIds' => 42],
                [
                    '$pull' => [
                        'normalizedData.color-en_US' => [
                            '$and' => [
                                ['code' => 'blue'],
                                ['optionValues.fr_FR.value' => 'Bleu']
                            ]
                        ]
                    ]
                ],
                ['multiple' => true],
            ],
        ]);
    }
}
