<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Bundle\Extension;

use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\GetAttributeTranslations;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\RequestStack;

class RegisterIdentifierFiltersSpec extends ObjectBehavior
{
    public function let(
        GetAttributes $getAttributes,
        GetAttributeTranslations $getAttributeTranslations,
        UserContext $userContext,
        RequestParameters $requestParams,
        RequestStack $requestStack,
        UserInterface $user,
        LocaleInterface $locale,
        BuildBefore $buildBefore,
        DatagridConfiguration $datagridConfiguration,
    ) {
        $locale->getCode()->willReturn('fr_FR');
        $user->getCatalogLocale()->willReturn($locale);
        $userContext->getUser()->willReturn($user);
        $buildBefore->getConfig()->willReturn($datagridConfiguration);

        $this->beConstructedWith($getAttributes, $getAttributeTranslations, $userContext, $requestParams, $requestStack);
    }

    public function it_adds_attribute_identifier_as_filters(
        BuildBefore $buildBefore,
        DatagridConfiguration $datagridConfiguration,
        GetAttributes $getAttributes,
        GetAttributeTranslations $getAttributeTranslations,
    ) {
        $getAttributeTranslations->byAttributeCodesAndLocale(['sku'], 'fr_FR')->willReturn([
            'sku' => 'Sku',
        ]);

        $getAttributes->forType('pim_catalog_identifier')->willReturn([
            'sku' => new Attribute(
                'sku',
                AttributeTypes::IDENTIFIER,
                [],
                false,
                false,
                null,
                null,
                false,
                AttributeTypes::BACKEND_TYPE_TEXT,
                [],
            ),
        ]);

        $familyFilter = [
            'type' => 'product_family',
            'label' => 'pim_datagrid.filters.family.label',
            'data_name' => 'family',
            'options' => [
                'field_options' => [
                    'multiple' => true,
                    'attr' => [
                        'empty_choice' => true,
                    ],
                ],
            ],
        ];

        $datagridConfiguration->offsetGet('filters')->willReturn(['columns' => ['family' => $familyFilter]]);
        $datagridConfiguration->offsetAddToArray('filters', [
            'columns' => [
                'family' => $familyFilter,
                'sku' => [
                    'type' => 'product_value_string',
                    'ftype' => 'identifier',
                    'label' => 'Sku',
                    'data_name' => 'sku',
                    'options' => [
                        'field_options' => [
                            'attr' => [
                                'choice_list' => true,
                                'empty_choice' => true,
                            ],
                        ],
                    ],
                ],
            ]
        ])->shouldBeCalled();

        $this->buildBefore($buildBefore);
    }

    public function it_falls_back_to_attribute_code_if_label_is_not_found(
        BuildBefore $buildBefore,
        DatagridConfiguration $datagridConfiguration,
        UserInterface $user,
        GetAttributes $getAttributes,
        GetAttributeTranslations $getAttributeTranslations,
    ) {
        $user->getCatalogLocale()->willReturn(null);
        $getAttributeTranslations->byAttributeCodesAndLocale(['sku'], 'en_US')->willReturn([]);

        $getAttributes->forType('pim_catalog_identifier')->willReturn([
            'sku' => new Attribute(
                'sku',
                AttributeTypes::IDENTIFIER,
                [],
                false,
                false,
                null,
                null,
                false,
                AttributeTypes::BACKEND_TYPE_TEXT,
                [],
            ),
        ]);

        $familyFilter = [
            'type' => 'product_family',
            'label' => 'pim_datagrid.filters.family.label',
            'data_name' => 'family',
            'options' => [
                'field_options' => [
                    'multiple' => true,
                    'attr' => [
                        'empty_choice' => true,
                    ],
                ],
            ],
        ];

        $datagridConfiguration->offsetGet('filters')->willReturn(['columns' => ['family' => $familyFilter]]);
        $datagridConfiguration->offsetAddToArray('filters', [
            'columns' => [
                'family' => $familyFilter,
                'sku' => [
                    'type' => 'product_value_string',
                    'ftype' => 'identifier',
                    'label' => '[sku]',
                    'data_name' => 'sku',
                    'options' => [
                        'field_options' => [
                            'attr' => [
                                'choice_list' => true,
                                'empty_choice' => true,
                            ],
                        ],
                    ],
                ],
            ]
        ])->shouldBeCalled();

        $this->buildBefore($buildBefore);
    }
}
