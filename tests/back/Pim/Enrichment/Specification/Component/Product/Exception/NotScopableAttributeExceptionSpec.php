<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Exception;

use Akeneo\Pim\Enrichment\Component\Product\Exception\NotScopableAttributeException;
use PhpSpec\ObjectBehavior;

class NotScopableAttributeExceptionSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedThrough('withCode', ['description']);
    }

    public function it_is_a_not_scopable_attribute_exception(): void
    {
        $this->shouldHaveType(NotScopableAttributeException::class);
    }

    public function it_provides_a_message(): void
    {
        $this->getMessage()->shouldReturn('The description attribute does not require a value per channel.');
    }
}
