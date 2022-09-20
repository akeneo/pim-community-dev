<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FreeTextSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedThrough('fromString', ['ABC']);
    }

    function it_is_a_free_text()
    {
        $this->shouldBeAnInstanceOf(FreeText::class);
    }

    function it_cannot_be_instantiated_with_an_empty_string()
    {
        $this->beConstructedThrough('fromString', ['']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_instantiated_with_blank_string()
    {
        $this->beConstructedThrough('fromString', ['  ']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_represents_a_free_text()
    {
        $this->asString()->shouldReturn('ABC');
    }
}
