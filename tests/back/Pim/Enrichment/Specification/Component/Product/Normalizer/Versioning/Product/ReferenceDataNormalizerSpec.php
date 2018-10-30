<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\Product;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ReferenceDataNormalizerSpec extends ObjectBehavior
{
    function it_is_a_normalizer()
    {
        $this->shouldBeAnInstanceOf(NormalizerInterface::class);
    }

    function it_supports_csv_normalization_reference_data(ReferenceDataInterface $referenceData)
    {
        $this->supportsNormalization($referenceData, 'csv')->shouldBe(true);
    }

    function it_normalizes_reference_data_using_the_default_format(ReferenceDataInterface $referenceData)
    {
        $referenceData->getCode()->willReturn('my_code');

        $this
            ->normalize($referenceData, null, ['field_name' => 'color'])
            ->shouldReturn(['color' => 'my_code']);
    }
}
