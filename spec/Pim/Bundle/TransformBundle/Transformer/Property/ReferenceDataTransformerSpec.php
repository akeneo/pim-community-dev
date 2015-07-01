<?php

namespace spec\Pim\Bundle\TransformBundle\Transformer\Property;

use PhpSpec\ObjectBehavior;
use Pim\Component\ReferenceData\ConfigurationRegistryInterface;

class ReferenceDataTransformerSpec extends ObjectBehavior
{
    function let(ConfigurationRegistryInterface $registry)
    {
        $this->beConstructedWith($registry);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\TransformBundle\Transformer\Property\ReferenceDataTransformer');
    }

    function it_returns_null_without_value()
    {
        $this->transform('', [])->shouldReturn(null);
        $this->transform(null, [])->shouldReturn(null);
    }

    function it_returns_a_registered_value($registry)
    {
        $value = 'color';
        $registry->has($value)->willReturn(true);

        $this->transform($value, [])->shouldReturn($value);
    }

    function it_throws_an_exception_with_unregistered_value($registry)
    {
        $value = 'color';
        $referenceData = ['car' => [], 'fabrics' => []];
        $registry->has($value)->willReturn(false);
        $registry->all()->willReturn($referenceData);

        $exception = new \InvalidArgumentException(sprintf(
            'Reference data "%s" does not exist. Allowed values are: %s',
            $value,
            'car, fabrics'
        ));

        $this->shouldThrow($exception)->during('transform', [$value]);
    }
}
