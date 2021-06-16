<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Exception;

use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessageInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\LocalizableAttributeException;
use PhpSpec\ObjectBehavior;

class LocalizableAttributeExceptionSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedThrough('withCode', ['description']);
    }

    public function it_is_a_domain_and_templated_error_exception(): void
    {
        $this->shouldHaveType(LocalizableAttributeException::class);
        $this->shouldImplement(DomainErrorInterface::class);
        $this->shouldImplement(TemplatedErrorMessageInterface::class);
    }

    public function it_provides_a_templated_error_message(): void
    {
        $templatedMessage = $this->getTemplatedErrorMessage();
        $templatedMessage->getTemplate()->shouldReturn('The {attribute_code} attribute requires a locale.');
        $templatedMessage->getParameters()->shouldReturn(['attribute_code' => 'description']);
    }

    public function it_provides_the_attribute_code(): void
    {
        $this->getAttributeCode()->shouldReturn('description');
    }
}
