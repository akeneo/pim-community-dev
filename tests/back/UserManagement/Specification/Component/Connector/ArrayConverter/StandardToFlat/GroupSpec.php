<?php
declare(strict_types=1);

namespace Specification\Akeneo\UserManagement\Component\Connector\ArrayConverter\StandardToFlat;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\UserManagement\Component\Connector\ArrayConverter\StandardToFlat\Group;
use PhpSpec\ObjectBehavior;

class GroupSpec extends ObjectBehavior
{
    function it_is_an_array_converter()
    {
        $this->shouldBeAnInstanceOf(Group::class);
        $this->shouldImplement(ArrayConverterInterface::class);
    }

    function it_converts_the_group_form_standard_to_flat()
    {
        $item = ['name' => 'the name'];

        $this->convert($item)->shouldBe(['name' => 'the name']);
    }
}
