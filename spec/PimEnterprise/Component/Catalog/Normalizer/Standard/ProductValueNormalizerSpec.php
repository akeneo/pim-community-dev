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

namespace spec\PimEnterprise\Component\Catalog\Normalizer\Standard;

use Akeneo\Asset\Bundle\AttributeType\AttributeTypes;
use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\ReferenceData\Value\ReferenceDataCollectionValueInterface;
use PimEnterprise\Component\Catalog\Normalizer\Standard\ProductValueNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class ProductValueNormalizerSpec extends ObjectBehavior
{
    function let(SerializerInterface $serializer)
    {
        $serializer->implement(NormalizerInterface::class);
        $this->setSerializer($serializer);
    }

    function it_is_a_product_value_normalizer()
    {
        $this->shouldHaveType(ProductValueNormalizer::class);
        $this->shouldImplement(NormalizerInterface::class);
        $this->shouldImplement(SerializerAwareInterface::class);
    }

    function it_supports_standard_format_and_product_value(ValueInterface $value)
    {
        $this->supportsNormalization($value, 'standard')->shouldReturn(true);
        $this->supportsNormalization($value, 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'other_format')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'standard')->shouldReturn(false);
    }

    function it_normalizes_an_ordered_asset_collection(
        $serializer,
        ReferenceDataCollectionValueInterface $value,
        AttributeInterface $attribute,
        AssetInterface $asset1,
        AssetInterface $asset2,
        AssetInterface $asset3
    ) {
        $asset1->getCode()->willReturn('first_asset');
        $asset2->getCode()->willReturn('second_asset');
        $asset3->getCode()->willReturn('third_asset');
        $serializer->normalize($asset1, null, [])->shouldNotBeCalled();
        $serializer->normalize($asset2, null, [])->shouldNotBeCalled();
        $serializer->normalize($asset3, null, [])->shouldNotBeCalled();

        $values = [$asset2, $asset1, $asset3];

        $value->getData()->willReturn($values);
        $value->getLocale()->willReturn(null);
        $value->getScope()->willReturn(null);
        $value->getAttribute()->willReturn($attribute);

        $attribute->isBackendTypeReferenceData()->willReturn(true);
        $attribute->getType()->willReturn(AttributeTypes::ASSETS_COLLECTION);
        $attribute->isDecimalsAllowed()->willReturn(false);

        $this->normalize($value)->shouldReturn(
            [
                'locale' => null,
                'scope'  => null,
                'data'   => ['second_asset', 'first_asset', 'third_asset'],
            ]
        );
    }
}
