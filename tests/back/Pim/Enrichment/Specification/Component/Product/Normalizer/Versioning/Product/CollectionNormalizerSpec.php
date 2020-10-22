<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\Product;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CollectionNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $normalizer)
    {
        $this->setNormalizer($normalizer);
    }

    function it_is_a_normalizer_aware_normalizer()
    {
        $this->shouldBeAnInstanceOf(NormalizerInterface::class);
        $this->shouldImplement(NormalizerAwareInterface::class);
    }

    function it_supports_flat_normalization_of_collection(Collection $collection)
    {
        $this->supportsNormalization($collection, 'flat')->shouldBe(true);
    }

    function it_does_not_support_csv_normalization_of_integer()
    {
        $this->supportsNormalization(1, 'csv')->shouldBe(false);
    }

    function it_normalizes_collection_of_array_elements(
        NormalizerInterface $normalizer,
        Collection $collection
    ) {
        $collection->getIterator()->willReturn(new \ArrayIterator([4, 8, 15]));
        $normalizer->normalize(4, null, [])->willReturn(['1st' => 'Four']);
        $normalizer->normalize(8, null, [])->willReturn(['2nd' => 'Eight']);
        $normalizer->normalize(15, null, [])->willReturn(['3rd' => 'Fifteen']);

        $this->normalize($collection)->shouldReturn([
            '1st' => 'Four',
            '2nd' => 'Eight',
            '3rd' => 'Fifteen',
        ]);
    }

    function it_normalizes_collection_of_scalar_elements(
        NormalizerInterface $normalizer,
        Collection $collection
    ) {
        $collection->getIterator()->willReturn(new \ArrayIterator([4, 8, 15]));
        $normalizer->normalize(4, null, ['field_name' => 'even'])->willReturn('Four');
        $normalizer->normalize(8, null, ['field_name' => 'even'])->willReturn('Eight');
        $normalizer->normalize(15, null, ['field_name' => 'even'])->willReturn('Fifteen');

        $this->normalize($collection, null, ['field_name' => 'even'])->shouldReturn(['even' => 'Four,Eight,Fifteen']);
    }

    function it_concatenates_normalized_elements_using_the_same_key(
        NormalizerInterface $normalizer,
        Collection $collection
    ) {
        $collection->getIterator()->willReturn(new \ArrayIterator([4, 8, 15]));
        $normalizer->normalize(4, null, ['field_name' => 'even'])->willReturn(['even' => 'Four']);
        $normalizer->normalize(8, null, ['field_name' => 'even'])->willReturn(['even' => 'Eight']);
        $normalizer->normalize(15, null, ['field_name' => 'even'])->willReturn(['even' => 'Fifteen']);

        $this->normalize($collection, null, ['field_name' => 'even'])->shouldReturn(['even' => 'Four,Eight,Fifteen']);
    }

    function it_normalizes_method_throw_exception_when_required_field_name_key_is_not_passed(
        NormalizerInterface $normalizer,
        Collection $collection
    ) {
        $collection->getIterator()->willReturn(new \ArrayIterator([4, 8, 15]));
        $normalizer->normalize(4, null, ['foo' => 'bar'])->willReturn('Four');
        $normalizer->normalize(8, null, ['foo' => 'bar'])->willReturn('Eight');
        $normalizer->normalize(15, null, ['foo' => 'bar'])->willReturn('Fifteen');

        $this
            ->shouldThrow(new InvalidArgumentException('Missing required "field_name" context value, got "foo"'))
            ->duringNormalize($collection, null, ['foo' => 'bar']);
    }

    function it_normalizes_attribute_with_numeric_code(
        NormalizerInterface $normalizer,
        Collection $collection
    ) {
        $collection->getIterator()->willReturn(new \ArrayIterator([4, 8, 15]));
        $normalizer->normalize(4, null, ['field_name' => 98796])->willReturn([98796 => 'Four']);
        $normalizer->normalize(8, null, ['field_name' => 98796])->willReturn([12345 => 'Eight']);
        $normalizer->normalize(15, null, ['field_name' => 98796])->willReturn([78901 => 'Fifteen']);

        $this->normalize($collection, null, ['field_name' => 98796])->shouldReturn([
            98796 => 'Four',
            12345 => 'Eight',
            78901 => 'Fifteen',
        ]);
    }
}
