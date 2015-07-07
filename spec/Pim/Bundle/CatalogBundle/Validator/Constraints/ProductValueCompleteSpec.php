<?php

namespace spec\Pim\Bundle\CatalogBundle\Validator\Constraints;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ChannelInterface;

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
                        'Expecting $channel to be an instance of "Pim\Bundle\CatalogBundle\Model\ChannelInterface", got "%s"',
                        'ecommerce'
                    )
                )
            )
            ->duringGetChannel();
    }

    function it_has_a_channel(ChannelInterface $channel)
    {
        $this->beConstructedWith(['channel' => $channel]);

        $this->getChannel()->shouldReturn($channel);
    }

    function it_has_message_complete(ChannelInterface $channel)
    {
        $this->beConstructedWith(['channel' => $channel]);

        $this->messageComplete->shouldBe('This value should be complete');
    }

    function it_has_message_not_null(ChannelInterface $channel)
    {
        $this->beConstructedWith(['channel' => $channel]);

        $this->messageNotNull->shouldBe('This value should not be null');
    }
}
