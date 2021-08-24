<?php
declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MessengerBundle\Ordering;

use Akeneo\Tool\Bundle\MessengerBundle\Ordering\ClassOrderingKeyResolver;
use Akeneo\Tool\Bundle\MessengerBundle\Ordering\OrderingKeyResolverInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Messenger\Envelope;

class ClassOrderingKeyResolverSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(\stdClass::class, 'the_key');
    }

    function it_is_a_ordering_key_candidate()
    {
        $this->shouldImplement(OrderingKeyResolverInterface::class);
        $this->shouldBeAnInstanceOf(ClassOrderingKeyResolver::class);
    }

    function it_supports_an_envelope_with_std_class_only()
    {
        $this->supports(new Envelope(new \stdClass))->shouldBe(true);
        $this->supports(new Envelope(new class{}))->shouldBe(false);
    }


    function it_returns_the_key()
    {
        $this->resolve(new Envelope(new \stdClass))->shouldBe('the_key');
    }
}
