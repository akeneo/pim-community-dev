<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Normalizer;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Model\Read\Attribute;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Normalizer\IdentifiersMappingNormalizer;
use PhpSpec\ObjectBehavior;

/**
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class IdentifiersMappingNormalizerSpec extends ObjectBehavior
{
    public function it_is_subscription_collection(): void
    {
        $this->shouldHaveType(IdentifiersMappingNormalizer::class);
    }

    public function it_normalizes_identifiers_mapping(
        Attribute $attributeSku,
        Attribute $attributeBrand
    ): void {
        $attributeSku->getCode()->willReturn(new AttributeCode('sku'));
        $attributeSku->getLabels()->willReturn([]);

        $attributeBrand->getCode()->willReturn(new AttributeCode('brand_code'));
        $attributeBrand->getLabels()->willReturn([
            'fr_FR' => 'Marque',
            'en_US' => 'Brand',
        ]);

        $mapping = new IdentifiersMapping([]);
        $mapping
            ->map('mpn', $attributeSku->getWrappedObject())
            ->map('brand', $attributeBrand->getWrappedObject());

        $this->normalize($mapping)->shouldReturn(
            [
                [
                    'from' => ['id' => 'brand'],
                    'status' => 'active',
                    'to' => [
                        'id' => 'brand_code',
                        'label' => [
                            'fr_FR' => 'Marque',
                            'en_US' => 'Brand',
                        ],
                    ],
                ],
                [
                    'from' => ['id' => 'mpn'],
                    'status' => 'active',
                    'to' => [
                        'id' => 'sku',
                        'label' => [],
                    ],
                ],
                [
                    'from' => ['id' => 'upc'],
                    'status' => 'inactive',
                    'to' => null,
                ],
                [
                    'from' => ['id' => 'asin'],
                    'status' => 'inactive',
                    'to' => null,
                ],
            ]
        );
    }
}
