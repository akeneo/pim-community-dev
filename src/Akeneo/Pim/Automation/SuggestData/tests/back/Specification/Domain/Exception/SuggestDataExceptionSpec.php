<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\SuggestData\Domain\Exception;

use Akeneo\Pim\Automation\SuggestData\Domain\Exception\SuggestDataException;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SuggestDataExceptionSpec extends ObjectBehavior
{
    function it_is_a_suggest_data_exception()
    {
        $this->shouldBeAnInstanceOf(SuggestDataException::class);
    }

    function it_is_an_exception()
    {
        $this->shouldBeAnInstanceOf(\Exception::class);
    }
}
