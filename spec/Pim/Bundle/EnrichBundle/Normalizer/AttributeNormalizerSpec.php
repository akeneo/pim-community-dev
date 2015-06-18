<?php

namespace spec\Pim\Bundle\EnrichBundle\Normalizer;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AttributeNormalizerSpec extends ObjectBehavior
{
    public function let(NormalizerInterface $normalizer)
    {
        $this->beConstructedWith($normalizer);
    }

    public function it_adds_the_attribute_id_to_the_normalized_attribute($normalizer, AttributeInterface $price)
    {
        $normalizer->normalize($price, 'json', [])->willReturn(['code' => 'price']);
        $price->getId()->willReturn(12);
        $price->isWysiwygEnabled()->willReturn(false);
        $price->getAttributeType()->willReturn('pim_catalog_text');

        $this->normalize($price, 'internal_api', [])->shouldReturn(['code' => 'price', 'id' => 12, 'wysiwyg_enabled' => false, 'empty_value' => '']);
    }

    public function it_adds_the_attribute_empty_value_to_the_normalized_attribute($normalizer, AttributeInterface $price)
    {
        $normalizer->normalize($price, 'json', [])->willReturn(['code' => 'text']);
        $price->getId()->willReturn(12);
        $price->isWysiwygEnabled()->willReturn(true);
        $price->getAttributeType()->willReturn('pim_catalog_textarea');

        $this->normalize($price, 'internal_api', [])->shouldReturn([
            'code'            => 'text',
            'id'              => 12,
            'wysiwyg_enabled' => true,
            'empty_value'     => null
        ]);

        $normalizer->normalize($price, 'json', [])->willReturn(['code' => 'boolean']);
        $price->getId()->willReturn(12);
        $price->isWysiwygEnabled()->willReturn(true);
        $price->getAttributeType()->willReturn('pim_catalog_boolean');

        $this->normalize($price, 'internal_api', [])->shouldReturn([
            'code'            => 'boolean',
            'id'              => 12,
            'wysiwyg_enabled' => true,
            'empty_value'     => false
        ]);

        $normalizer->normalize($price, 'json', [])->willReturn(['code' => 'collection']);
        $price->getId()->willReturn(12);
        $price->isWysiwygEnabled()->willReturn(false);
        $price->getAttributeType()->willReturn('pim_catalog_price_collection');

        $this->normalize($price, 'internal_api', [])->shouldReturn([
            'code'            => 'collection',
            'id'              => 12,
            'wysiwyg_enabled' => false,
            'empty_value'     => []
        ]);

        $normalizer->normalize($price, 'json', [])->willReturn(['code' => 'collection']);
        $price->getId()->willReturn(12);
        $price->isWysiwygEnabled()->willReturn(false);
        $price->getAttributeType()->willReturn('pim_catalog_multiselect');

        $this->normalize($price, 'internal_api', [])->shouldReturn([
            'code'            => 'collection',
            'id'              => 12,
            'wysiwyg_enabled' => false,
            'empty_value'     => []
        ]);

        $normalizer->normalize($price, 'json', [])->willReturn(['code' => 'metric']);
        $price->getId()->willReturn(12);
        $price->isWysiwygEnabled()->willReturn(false);
        $price->getAttributeType()->willReturn('pim_catalog_metric');
        $price->getDefaultMetricUnit()->willReturn('kg');

        $this->normalize($price, 'internal_api', [])->shouldReturn([
            'code'            => 'metric',
            'id'              => 12,
            'wysiwyg_enabled' => false,
            'empty_value'     => [
                'data' => null,
                'unit' => 'kg'
            ]
        ]);

        $normalizer->normalize($price, 'json', [])->willReturn(['code' => 'default']);
        $price->getId()->willReturn(12);
        $price->isWysiwygEnabled()->willReturn(false);
        $price->getAttributeType()->willReturn('unknown');

        $this->normalize($price, 'internal_api', [])->shouldReturn([
            'code'            => 'default',
            'id'              => 12,
            'wysiwyg_enabled' => false,
            'empty_value'     => null
        ]);
    }

    public function it_supports_attributes_and_internal_api(AttributeInterface $price)
    {
        $this->supportsNormalization($price, 'internal_api')->shouldReturn(true);
        $this->supportsNormalization($price, 'json')->shouldReturn(false);
        $this->supportsNormalization(new \StdClass(), 'internal_api')->shouldReturn(false);
    }
}
