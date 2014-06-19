<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Entity;

use PhpSpec\ObjectBehavior;
use Oro\Bundle\UserBundle\Entity\Role;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;

class CategoryOwnershipSpec extends ObjectBehavior
{
    function it_stores_a_role_and_a_category(Role $role, CategoryInterface $category)
    {
        $this->getRole()->shouldReturn(null);
        $this->setRole($role)->shouldReturn($this);
        $this->getRole()->shouldReturn($role);

        $this->getCategory()->shouldReturn(null);
        $this->setCategory($category)->shouldReturn($this);
        $this->getCategory()->shouldReturn($category);
    }
}
