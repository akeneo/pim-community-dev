<?php

namespace spec\Pim\Bundle\VersioningBundle\Normalizer\Flat;

use Doctrine\Common\Collections\Collection;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\SerializerInterface;

class CollectionNormalizerSpec extends ObjectBehavior
{
    function let(SerializerInterface $serializer)
    {
        $serializer->implement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->setSerializer($serializer);
    }

    function it_is_a_serializer_aware_normalizer()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->shouldBeAnInstanceOf('Symfony\Component\Serializer\SerializerAwareInterface');
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

    function it_concatenate_normalized_elements_using_the_same_key(
        $serializer,
        Collection $collection
    ) {
        $collection->getIterator()->willReturn(new \ArrayIterator([4, 8, 15]));
        $serializer->normalize(4, null, ['field_name' => 'even'])->willReturn(['even' => 'Four']);
        $serializer->normalize(8, null, ['field_name' => 'even'])->willReturn(['even' => 'Eight']);
        $serializer->normalize(15, null, ['field_name' => 'even'])->willReturn(['even' => 'Fifteen']);

        $this->normalize($collection, null, ['field_name' => 'even'])->shouldReturn(['even' => 'Four,Eight,Fifteen']);
    }

    function its_normalize_method_throw_exception_when_required_field_name_key_is_not_passed(
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
}
