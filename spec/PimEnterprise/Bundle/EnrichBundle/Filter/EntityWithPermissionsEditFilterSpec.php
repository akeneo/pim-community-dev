<?php

namespace spec\PimEnterprise\Bundle\EnrichBundle\Filter;

use PhpSpec\ObjectBehavior;

class EntityWithPermissionsEditFilterSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(['last_name']);
    }

    function it_supports_array_as_input()
    {
        $this->supportsCollection(['first_name' => 'denis'], 'type')->shouldReturn(true);
        $this->supportsCollection('not_an_array', 'type')->shouldReturn(false);
    }

    function it_excludes_specified_keys_from_input()
    {
        $this->filterCollection([
            'first_name' => 'denis',
            'last_name'  => 'brogniart',
            'comment'    => 'ah!',
        ], 'type')->shouldReturn([
            'first_name' => 'denis',
            'comment'    => 'ah!',
        ]);
    }
}
