<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Exception;

use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessageInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnknownProductException;
use PhpSpec\ObjectBehavior;

class UnknownProductExceptionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('product_identifier');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UnknownProductException::class);
    }

    function it_is_an_exception()
    {
        $this->shouldHaveType(\Exception::class);
    }

    function it_is_a_domain_error()
    {
        $this->shouldImplement(DomainErrorInterface::class);
    }

    function it_is_has_a_templated_error_message()
    {
        $this->shouldImplement(TemplatedErrorMessageInterface::class);
    }

    function it_returns_an_exception_message()
    {
        $this->getMessage()->shouldReturn('The product_identifier product does not exist in your PIM.');
    }

    function it_returns_a_message_template()
    {
        $this->getMessageTemplate()->shouldReturn('The %s product does not exist in your PIM.');
    }

    function it_returns_message_parameters()
    {
        $this->getMessageParameters()->shouldReturn(['product_identifier']);
    }
}
