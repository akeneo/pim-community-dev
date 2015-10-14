<?php

namespace spec\Pim\Bundle\UserBundle\Entity;

use Pim\Bundle\UserBundle\Entity\Role;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * Class GroupSpec
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupSpec extends ObjectBehavior
{
    function it_gets_roles_as_string(Role $roleUser, Role $roleAdmin)
    {
        $roleUser->getLabel()->willReturn('User');
        $roleAdmin->getLabel()->willReturn('Administrator');

        $this->setRoles([$roleUser, $roleAdmin]);
        $this->getRoleLabelsAsString()->shouldReturn('User, Administrator');
    }

    function it_has_roles_by_role(Role $roleUser)
    {
        $this->setRoles([$roleUser]);
        $this->hasRole($roleUser)->shouldReturn(true);
    }

    function it_has_roles_by_string(Role $roleUser)
    {
        $roleUser->getRole()->willReturn('ROLE_USER');
        $roleUser->getLabel()->willReturn('ROLE_USER');

        $this->setRoles([$roleUser]);
        $this->hasRole($roleUser)->shouldReturn(true);
    }
}
