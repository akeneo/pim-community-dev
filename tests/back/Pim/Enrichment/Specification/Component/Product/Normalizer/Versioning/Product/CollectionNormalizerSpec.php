<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Versioning\Product;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

class CollectionNormalizerSpec extends ObjectBehavior
{
    function let(SerializerInterface $serializer)
    {
        $serializer->implement(NormalizerInterface::class);
        $this->setSerializer($serializer);
    }

    function it_is_a_serializer_aware_normalizer()
    {
        $this->shouldBeAnInstanceOf(NormalizerInterface::class);
        $this->shouldBeAnInstanceOf(SerializerAwareInterface::class);
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
        $serializer,
        Collection $collection
    ) {
        $collection->getIterator()->willReturn(new \ArrayIterator([4, 8, 15]));
        $serializer->normalize(4, null, [])->willReturn(['1st' => 'Four']);
        $serializer->normalize(8, null, [])->willReturn(['2nd' => 'Eight']);
        $serializer->normalize(15, null, [])->willReturn(['3rd' => 'Fifteen']);

        $this->normalize($collection)->shouldReturn([
            '1st' => 'Four',
            '2nd' => 'Eight',
            '3rd' => 'Fifteen',
        ]);
    }

    function it_normalizes_collection_of_scalar_elements(
        $serializer,
        Collection $collection
    ) {
        $collection->getIterator()->willReturn(new \ArrayIterator([4, 8, 15]));
        $serializer->normalize(4, null, ['field_name' => 'even'])->willReturn('Four');
        $serializer->normalize(8, null, ['field_name' => 'even'])->willReturn('Eight');
        $serializer->normalize(15, null, ['field_name' => 'even'])->willReturn('Fifteen');

        $this->normalize($collection, null, ['field_name' => 'even'])->shouldReturn(['even' => 'Four,Eight,Fifteen']);
    }

    function it_concatenates_normalized_elements_using_the_same_key(
        $serializer,
        Collection $collection
    ) {
        $collection->getIterator()->willReturn(new \ArrayIterator([4, 8, 15]));
        $serializer->normalize(4, null, ['field_name' => 'even'])->willReturn(['even' => 'Four']);
        $serializer->normalize(8, null, ['field_name' => 'even'])->willReturn(['even' => 'Eight']);
        $serializer->normalize(15, null, ['field_name' => 'even'])->willReturn(['even' => 'Fifteen']);

        $this->normalize($collection, null, ['field_name' => 'even'])->shouldReturn(['even' => 'Four,Eight,Fifteen']);
    }

    function it_normalizes_method_throw_exception_when_required_field_name_key_is_not_passed(
        $serializer,
        Collection $collection
    ) {
        $collection->getIterator()->willReturn(new \ArrayIterator([4, 8, 15]));
        $serializer->normalize(4, null, ['foo' => 'bar'])->willReturn('Four');
        $serializer->normalize(8, null, ['foo' => 'bar'])->willReturn('Eight');
        $serializer->normalize(15, null, ['foo' => 'bar'])->willReturn('Fifteen');

        $this
            ->shouldThrow(new InvalidArgumentException('Missing required "field_name" context value, got "foo"'))
            ->duringNormalize($collection, null, ['foo' => 'bar']);
    }

    function it_normalizes_attribute_with_numeric_code(
        $serializer,
        Collection $collection
    ) {
        $collection->getIterator()->willReturn(new \ArrayIterator([4, 8, 15]));
        $serializer->normalize(4, null, ['field_name' => 98796])->willReturn([98796 => 'Four']);
        $serializer->normalize(8, null, ['field_name' => 98796])->willReturn([12345 => 'Eight']);
        $serializer->normalize(15, null, ['field_name' => 98796])->willReturn([78901 => 'Fifteen']);

        $this->normalize($collection, null, ['field_name' => 98796])->shouldReturn([
            98796 => 'Four',
            12345 => 'Eight',
            78901 => 'Fifteen',
        ]);
    }
}
