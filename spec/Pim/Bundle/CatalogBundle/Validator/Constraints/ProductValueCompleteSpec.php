<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Bundle\CatalogBundle\Entity\Channel;

class ProductValueCompleteSpec extends ObjectBehavior
{
    function it_is_a_constraint()
    {
        $this->shouldHaveType('Symfony\Component\Validator\Constraint');
    }

    function it_must_be_constructed_with_a_channel_option()
    {
        $this->shouldThrow('\Symfony\Component\Validator\Exception\MissingOptionsException')->during('__construct');
    }

    function its_getChannel_throws_exception_if_the_configured_channel_is_not_a_Channel_object()
    {
        $this->beConstructedWith(['channel' => 'ecommerce']);

        $this
            ->shouldThrow(
                new \LogicException(
                    sprintf(
                        'Expecting $channel to be an instance of "\Pim\Bundle\CatalogBundle\Entity\Channel", got "%s"',
                        'ecommerce'
                    )
                )
            )
            ->duringGetChannel();
    }

    function it_has_a_channel(Channel $channel)
    {
        $this->beConstructedWith(['channel' => $channel]);

        $this->getChannel()->shouldReturn($channel);
    }
}
