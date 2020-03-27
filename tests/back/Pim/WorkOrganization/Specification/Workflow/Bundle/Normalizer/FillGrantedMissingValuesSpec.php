<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Normalizer;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\ValuesFiller\FillMissingValuesInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class FillGrantedMissingValuesSpec extends ObjectBehavior
{
    public function let(
        FillMissingValuesInterface $baseFillMissingValues,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->beConstructedWith($baseFillMissingValues, $attributeRepository, $localeRepository, $authorizationChecker);
    }

    public function it_fills_granted_missing_values_from_standard_format(
        FillMissingValuesInterface $baseFillMissingValues,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        AuthorizationCheckerInterface $authorizationChecker,
        AttributeInterface $attributeSku,
        AttributeInterface $allowedAttributeA,
        AttributeInterface $allowedAttributeB,
        AttributeInterface $notAllowedAttribute,
        LocaleInterface $localeEn,
        LocaleInterface $localeFr
    ) {
        $standardFormat = [
            'identifier' => 'a_product',
            'values' => [
                'sku' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'value' => 'a_product',
                    ]
                ]
            ]
        ];

        $standardFormatWithMissingValues = $standardFormat;
        $standardFormatWithMissingValues['values']['allowed_attribute_A'] = [
            [
                'scope' => null,
                'locale' => 'en_US',
                'data' => null,
            ],
            [
                'scope' => null,
                'locale' => 'fr_FR',
                'data' => null,
            ]
        ];
        $standardFormatWithMissingValues['values']['allowed_attribute_B'] = [
            [
                'scope' => null,
                'locale' => 'en_US',
                'data' => null,
            ],
            [
                'scope' => null,
                'locale' => 'fr_FR',
                'data' => null,
            ]
        ];
        $standardFormatWithMissingValues['values']['not_allowed_attribute'] = [
            [
                'scope' => null,
                'locale' => null,
                'data' => null,
            ],
        ];

        $baseFillMissingValues->fromStandardFormat($standardFormat)->willReturn($standardFormatWithMissingValues);

        $attributeRepository->findOneByIdentifier('sku')->willReturn($attributeSku);
        $attributeRepository->findOneByIdentifier('allowed_attribute_A')->willReturn($allowedAttributeA);
        $attributeRepository->findOneByIdentifier('allowed_attribute_B')->willReturn($allowedAttributeB);
        $attributeRepository->findOneByIdentifier('not_allowed_attribute')->willReturn($notAllowedAttribute);

        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $attributeSku)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $allowedAttributeA)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $allowedAttributeB)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $notAllowedAttribute)->willReturn(false);

        $localeRepository->findOneByIdentifier('en_US')->willReturn($localeEn);
        $localeRepository->findOneByIdentifier('fr_FR')->willReturn($localeFr);

        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $localeEn)->willReturn(true);
        $authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $localeFr)->willReturn(false);

        $this->fromStandardFormat($standardFormat)->shouldBeLike([
            'identifier' => 'a_product',
            'values' => [
                'sku' => [
                    [
                        'scope' => null,
                        'locale' => null,
                        'value' => 'a_product'
                    ]
                ],
                'allowed_attribute_A' => [
                    [
                        'scope' => null,
                        'locale' => 'en_US',
                        'data' => null,
                    ],
                ],
                'allowed_attribute_B' => [
                    [
                        'scope' => null,
                        'locale' => 'en_US',
                        'data' => null,
                    ],
                ]
            ]
        ]);
    }
}
