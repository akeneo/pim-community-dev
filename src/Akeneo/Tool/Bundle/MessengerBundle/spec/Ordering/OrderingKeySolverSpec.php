<?php
declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\MessengerBundle\Ordering;

use Akeneo\Tool\Bundle\MessengerBundle\Ordering\OrderingKeyResolverInterface;
use Akeneo\Tool\Bundle\MessengerBundle\Ordering\OrderingKeySolver;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Messenger\Envelope;

class OrderingKeySolverSpec extends ObjectBehavior
{
    function let(OrderingKeyResolverInterface $candidate1, OrderingKeyResolverInterface $candidate2)
    {
        $this->beConstructedWith([$candidate1, $candidate2]);
    }

    function it_can_be_instantiable()
    {
        $this->shouldBeAnInstanceOf(OrderingKeySolver::class);
    }

    function it_returns_null_when_no_candidates_support_the_envelope(
        OrderingKeyResolverInterface $candidate1,
        OrderingKeyResolverInterface $candidate2
    ) {
        $envelope = new Envelope(new \stdClass());

        $candidate1->supports($envelope)->willReturn(false);
        $candidate2->supports($envelope)->willReturn(false);

        $this->solve($envelope)->shouldBeNull();
    }

    function it_returns_the_key_when_a_candidate_supports_the_envelope(
        OrderingKeyResolverInterface $candidate1,
        OrderingKeyResolverInterface $candidate2
    ) {
        $envelope = new Envelope(new \stdClass());

        $candidate1->supports($envelope)->willReturn(false);
        $candidate2->supports($envelope)->willReturn(true);
        $candidate2->resolve($envelope)->willReturn('the_key');

        $this->solve($envelope)->shouldBe('the_key');
    }
}
