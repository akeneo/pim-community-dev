<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Permission;

use PhpSpec\ObjectBehavior;

class UserGroupIdentifierSpec extends ObjectBehavior
{
    function it_normalizes_itself()
    {
        $this->beConstructedThrough('fromInteger', [12]);

        $this->normalize()->shouldReturn(12);
    }
}
