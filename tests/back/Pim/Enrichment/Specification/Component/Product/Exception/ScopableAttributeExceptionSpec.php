<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Exception;

use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Error\TemplatedErrorMessage\TemplatedErrorMessageInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ScopableAttributeException;
use PhpSpec\ObjectBehavior;

class ScopableAttributeExceptionSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedThrough('withCode', ['description']);
    }

    public function it_is_a_scopable_attribute_exception(): void
    {
        $this->shouldHaveType(ScopableAttributeException::class);
    }

    public function it_provides_a_message(): void
    {
        $this->getMessage()->shouldReturn('The description attribute requires a value per channel.');
    }
}
