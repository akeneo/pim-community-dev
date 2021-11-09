<?php

namespace Specification\Akeneo\UserManagement\Bundle\Twig;

use PhpSpec\ObjectBehavior;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AclGroupsExtensionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith([]);
    }

    function it_is_a_twig_extension()
    {
        $this->shouldBeAnInstanceOf(AbstractExtension::class);
    }

    function it_provides_an_acl_groups_twig_function()
    {
        $functions = $this->getFunctions();

        $functions->shouldHaveCount(2);
        $functions[0]->shouldBeAnInstanceOf(TwigFunction::class);
        $functions[1]->shouldBeAnInstanceOf(TwigFunction::class);
    }

    function it_provides_a_sorted_list_of_defined_acl_groups()
    {
        // TODO: Find a way to spec this
        $this->getAclGroups()->shouldReturn([]);
    }
}
