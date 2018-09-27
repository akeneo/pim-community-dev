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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer\IdentifiersMappingNormalizer;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;

class IdentifiersMappingNormalizerSpec extends ObjectBehavior
{
    public function it_is_subscription_collection(): void
    {
        $this->shouldHaveType(IdentifiersMappingNormalizer::class);
    }

    public function it_should_normalize_identifiers_mapping(
        IdentifiersMapping $mapping,
        AttributeInterface $attributeSku,
        AttributeInterface $attributeBrand
    ): void {
        $mapping->getIdentifiers()->willReturn(
            [
                'mpn' => $attributeSku,
                'brand' => $attributeBrand,
                'ean' => null,
            ]
        );
        $attributeSku->setLocale('en_US')->shouldBeCalled();
        $attributeBrand->setLocale('en_US')->shouldBeCalled();
        $attributeSku->getCode()->willReturn('sku');
        $attributeBrand->getCode()->willReturn('brand_code');
        $attributeSku->getLabel()->willReturn('SKU');
        $attributeBrand->getLabel()->willReturn('Brand');

        $this->normalize($mapping)->shouldReturn(
            [
                'mpn' => [
                    'code' => 'sku',
                    'label' => ['en_US' => 'SKU'],
                ],
                'brand' => [
                    'code' => 'brand_code',
                    'label' => ['en_US' => 'Brand'],
                ],
            ]
        );
    }
}
