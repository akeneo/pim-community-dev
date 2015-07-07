<?php

namespace spec\PimEnterprise\Bundle\WorkflowBundle\Model;

use PhpSpec\ObjectBehavior;

class ProductDraftSpec extends ObjectBehavior
{
    function it_removes_category_id()
    {
        $this->setCategoryIds([4, 8, 15, 16, 23, 42]);
        $this->removeCategoryId(15);

        $this->getCategoryIds()->shouldReturn([4, 8, 16, 23, 42]);
    }

    function it_ignores_unknown_value_while_remove_category_id()
    {
        $this->setCategoryIds([4, 8, 15, 16, 23, 42]);
        $this->removeCategoryId(17);

        $this->getCategoryIds()->shouldReturn([4, 8, 15, 16, 23, 42]);
    }
}
