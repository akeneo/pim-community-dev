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
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Normalizer\IdentifiersMappingNormalizer;
use Akeneo\Test\Pim\Automation\FranklinInsights\Specification\Builder\AttributeBuilder;
use PhpSpec\ObjectBehavior;

/**
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class IdentifiersMappingNormalizerSpec extends ObjectBehavior
{
    public function let(AttributeRepositoryInterface $attributeRepository): void
    {
        $this->beConstructedWith($attributeRepository);
    }

    public function it_is_subscription_collection(): void
    {
        $this->shouldHaveType(IdentifiersMappingNormalizer::class);
    }

    public function it_normalizes_identifiers_mapping(
        $attributeRepository
    ): void {

        $attributeSku = (new AttributeBuilder())->withCode('sku')->withLabels([])->build();
        $attributeBrand = (new AttributeBuilder())->withCode('brand_code')->withLabels([
            'fr_FR' => 'Marque',
            'en_US' => 'Brand',
        ])->build();

        $attributeRepository->findByCodes(['brand_code', 'sku'])->willReturn([$attributeSku, $attributeBrand]);

        $mapping = new IdentifiersMapping([]);
        $mapping
            ->map('mpn', new AttributeCode('sku'))
            ->map('brand', new AttributeCode('brand_code'));

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
