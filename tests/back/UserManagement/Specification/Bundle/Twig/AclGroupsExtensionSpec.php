<?php

namespace Specification\Akeneo\UserManagement\Bundle\Twig;

use PhpSpec\ObjectBehavior;

class AclGroupsExtensionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([]);
    }

    function it_is_a_twig_extension()
    {
        $this->shouldBeAnInstanceOf(\Twig_Extension::class);
    }

    function it_provides_an_acl_groups_twig_function()
    {
        $functions = $this->getFunctions();

        $functions->shouldHaveCount(2);
        $functions[0]->shouldBeAnInstanceOf(\Twig_SimpleFunction::class);
        $functions[1]->shouldBeAnInstanceOf(\Twig_SimpleFunction::class);
    }

    function it_provides_a_sorted_list_of_defined_acl_groups()
    {
        // TODO: Find a way to spec this
        $this->getAclGroups()->shouldReturn([]);
    }
}
