<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing\ProductValue;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ValueInterface;
use Pim\Component\Catalog\Normalizer\Indexing\Product\ProductNormalizer;
use Pim\Component\Catalog\Normalizer\Indexing\ProductAndModel\ProductModelNormalizer;
use Pim\Component\Catalog\Normalizer\Indexing\ProductValue\DateNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DateNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(DateNormalizer::class);
    }

    function it_support_dates(ValueInterface $dateValue, AttributeInterface $attribute)
    {
        $dateValue->getAttribute()->willReturn($attribute);
        $attribute->getBackendType()->willReturn(AttributeTypes::BACKEND_TYPE_DATE);

        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'indexing')->shouldReturn(false);
        $this->supportsNormalization($dateValue, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($dateValue, 'indexing')->shouldReturn(true);
    }
}
