<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\Apps\ValueObject;

use Akeneo\Connectivity\Connection\Domain\Apps\ValueObject\ScopeList;
use PhpSpec\ObjectBehavior;

class ScopeListSpec extends ObjectBehavior
{
    public function it_is_a_scope_list(): void
    {
        $this->shouldHaveType(ScopeList::class);
    }

    public function it_is_instantiable_from_a_string_of_scopes(): void
    {
    }

    public function it_is_instantiable_from_an_array_of_scopes(): void
    {
    }
}
