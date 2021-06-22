<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Exception;

use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessage;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessageInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDateAttributeException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class InvalidDateAttributeExceptionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('date_attribute', 'yyyy-mm-dd', self::class);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(InvalidDateAttributeException::class);
    }

    function it_is_a_property_exception()
    {
        $this->shouldHaveType(InvalidPropertyException::class);
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

    function it_returns_a_property_name()
    {
        $this->getPropertyName()->shouldReturn('date_attribute');
    }

    function it_returns_a_message_template_and_parameters()
    {
        $templatedErrorMessage = $this->getTemplatedErrorMessage();
        $templatedErrorMessage->shouldBeAnInstanceOf(TemplatedErrorMessage::class);
        $templatedErrorMessage->__toString()
            ->shouldReturn('The date_attribute attribute requires a valid date. Please use the following format yyyy-mm-dd for dates.');
    }
}
