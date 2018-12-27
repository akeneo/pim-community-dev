<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Domain\Model\Permission;

use Akeneo\ReferenceEntity\Domain\Model\Permission\UserGroupIdentifier;
use PhpSpec\ObjectBehavior;

class UserGroupIdentifierSpec extends ObjectBehavior
{
    function it_normalizes_itself()
    {
        $this->beConstructedThrough('fromInteger', [12]);

        $this->normalize()->shouldReturn(12);
    }

    function it_can_compare_itself()
    {
        $this->beConstructedThrough('fromInteger', [12]);
        $this->equals(UserGroupIdentifier::fromInteger(12))->shouldReturn(true);
        $this->equals(UserGroupIdentifier::fromInteger(1))->shouldReturn(false);
    }
}
