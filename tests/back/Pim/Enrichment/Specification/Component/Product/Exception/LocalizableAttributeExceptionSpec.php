<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Exception;

use Akeneo\Pim\Enrichment\Component\Product\Exception\LocalizableAttributeException;
use PhpSpec\ObjectBehavior;

class LocalizableAttributeExceptionSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedThrough('withCode', ['description']);
    }

    public function it_is_a_localizable_attribute_exception(): void
    {
        $this->shouldHaveType(LocalizableAttributeException::class);
    }

    public function it_provides_a_message(): void
    {
        $this->getMessage()->shouldReturn('The description attribute requires a locale.');
    }
}
