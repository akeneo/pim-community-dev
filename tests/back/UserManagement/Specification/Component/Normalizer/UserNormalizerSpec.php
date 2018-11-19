<?php

namespace Specification\Akeneo\UserManagement\Component\Normalizer;

use Akeneo\UserManagement\Component\Normalizer\UserNormalizer;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UserNormalizerSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(
            'property_name',
            'other_property_name'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(UserNormalizer::class);
    }

    function it_is_user_normalizer()
    {
        $this->normalize()->shouldReturn([]);
    }
}
