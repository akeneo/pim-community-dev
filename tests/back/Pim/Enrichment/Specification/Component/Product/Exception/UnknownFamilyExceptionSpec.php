<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Exception;

use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnknownFamilyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use PhpSpec\ObjectBehavior;

class UnknownFamilyExceptionSpec extends ObjectBehavior
{
    public function let(): void
    {
        $this->beConstructedThrough('unknownFamily', ['family', 'family_code', self::class]);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(UnknownFamilyException::class);
    }

    public function it_is_an_invalid_property_exception(): void
    {
        $this->shouldHaveType(InvalidPropertyException::class);
    }

    public function it_is_an_identifiable_domain_error(): void
    {
        $this->shouldImplement(DomainErrorInterface::class);
    }

    // public function it_is_a_documented_error(): void
    // {
    //     $this->shouldImplement(DocumentedErrorInterface::class);
    // }

    // public function it_provides_documentation(): void
    // {
    // }
}
