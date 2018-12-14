<?php

namespace Specification\Akeneo\Pim\Permission\Component\Authorization\Model;

use Akeneo\Pim\Permission\Component\Authorization\Model\UserRightsOnProduct;
use Akeneo\Pim\Permission\Component\Authorization\Model\UserRightsOnProductModel;
use PhpSpec\ObjectBehavior;

class UserRightsOnProductModelSpec extends ObjectBehavior
{
    function it_is_an_authorization_checker(): void
    {
        $this->beConstructedWith('test', 1, 2, 3, 3);
        $this->shouldBeAnInstanceOf(UserRightsOnProductModel::class);
    }

    function it_is_a_product_model_that_the_user_can_edit_when_the_product_model_is_categorized_in_at_least_one_category_owned_by_the_user(): void {
        $this->beConstructedWith('test', 1, 2, 3, 3);
        $this->isProductModelEditable()->shouldReturn(true);
    }

    function it_is_a_product_model_that_the_user_can_edit_when_the_product_model_is_not_categorized(): void {
        $this->beConstructedWith('test', 1, 0, 0, 0);
        $this->isProductModelEditable()->shouldReturn(true);
    }

    function it_is_feasible_for_the_user_to_apply_a_draft_on_the_product_model_when_the_product_model_is_categorized_in_at_least_one_category_editable_and_no_category_owned_by_the_user(): void {
        $this->beConstructedWith('test', 1, 1, 0, 1);
        $this->canApplyDraftOnProductModel()->shouldReturn(true);
    }

    function it_is_not_possible_to_edit_a_product_model_when_the_user_can_apply_a_draft_on_the_product_model(): void {
        $this->beConstructedWith('test', 1, 1, 0, 1);
        $this->canApplyDraftOnProductModel()->shouldReturn(true);
        $this->isProductModelEditable()->shouldReturn(false);
    }

    function it_is_user_rights_for_a_given_product_model(): void {
        $this->beConstructedWith('test', 1, 0, 0, 0);
        $this->productModelCode()->shouldReturn('test');
    }

    function it_is_user_rights_for_a_given_user(): void {
        $this->beConstructedWith('test', 1, 0, 0, 0);
        $this->userId()->shouldReturn(1);
    }
}
