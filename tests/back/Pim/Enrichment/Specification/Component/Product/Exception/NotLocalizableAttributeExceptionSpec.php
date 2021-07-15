<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Exception;

use Akeneo\Pim\Enrichment\Component\Product\Exception\NotLocalizableAttributeException;
use PhpSpec\ObjectBehavior;

class NotLocalizableAttributeExceptionSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedThrough('withCode', ['description']);
    }

    public function it_is_a_not_localizable_attribute_exception(): void
    {
        $this->shouldHaveType(NotLocalizableAttributeException::class);
    }

    public function it_provides_a_message(): void
    {
        $this->getMessage()->shouldReturn('The description attribute is not localisable.');
    }
}
