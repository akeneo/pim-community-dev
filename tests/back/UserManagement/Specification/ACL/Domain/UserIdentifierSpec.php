<?php

declare(strict_types=1);

namespace Specification\Akeneo\UserManagement\ACL\Domain;

use Akeneo\UserManagement\ACL\Domain\UserIdentifier;
use PhpSpec\ObjectBehavior;

class UserIdentifierSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedThrough('fromString', ['julia']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UserIdentifier::class);
    }

    function it_returns_a_string_representation()
    {
        $this->stringValue()->shouldReturn('julia');
    }

    function it_throws_if_the_string_is_empty()
    {
        $this->shouldThrow(\InvalidArgumentException::class)
            ->during('fromString', ['']);
    }
}
