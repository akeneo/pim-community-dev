<?php

namespace spec\Akeneo\Tool\Component\Email;

use PhpSpec\ObjectBehavior;

class SenderAddressSpec extends ObjectBehavior
{
    function it_can_not_build_a_sender_address_from_a_wrong_url()
    {
        $this->beConstructedThrough('fromMailerUrl', ['wrong url']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_can_not_build_a_sender_address_if_the_url_does_not_contain_it()
    {
        $this->beConstructedThrough('fromMailerUrl', ['null://localhost?encryption=tls&auth_mode=login&username=foo&password=bar']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_can_not_build_a_sender_address_if_the_url_does_not_contain_a_valid_email_address()
    {
        $this->beConstructedThrough('fromMailerUrl', ['null://localhost?encryption=tls&auth_mode=login&username=foo&password=bar&sender_address=baz']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_builds_a_sender_address_from_the_url()
    {
        $this->beConstructedThrough('fromMailerUrl', ['null://localhost?encryption=tls&auth_mode=login&username=foo&password=bar&sender_address=no-reply@example.com']);
        $this->__toString()->shouldReturn('no-reply@example.com');
    }
}
