<?php

namespace Specification\Akeneo\Pim\Permission\Component\Authorization\Model;

use Akeneo\Pim\Permission\Component\Authorization\Model\UserRightsOnProductUuid;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

class UserRightsOnProductUuidSpec extends ObjectBehavior
{
    function it_is_an_authorization_checker(): void
    {
        $uuid = Uuid::uuid4();
        $this->beConstructedWith($uuid, 1, 2, 3, 1, 3);
        $this->shouldBeAnInstanceOf(UserRightsOnProductUuid::class);
    }

    function it_is_a_product_that_the_user_can_view_when_the_product_is_categorized_in_at_least_one_category_viewable_by_the_user(): void {
        $uuid = Uuid::uuid4();
        $this->beConstructedWith($uuid, 1, 0, 0, 1, 3);
        $this->isProductViewable()->shouldReturn(true);
    }

    function it_is_a_product_that_the_user_can_view_when_the_product_is_not_categorized(): void {
        $uuid = Uuid::uuid4();
        $this->beConstructedWith($uuid, 1, 0, 0, 0, 0);
        $this->isProductViewable()->shouldReturn(true);
    }

    function it_is_a_product_that_the_user_can_edit_when_the_product_is_categorized_in_at_least_one_category_owned_by_the_user(): void {
        $uuid = Uuid::uuid4();
        $this->beConstructedWith($uuid, 1, 2, 3, 1, 3);
        $this->isProductEditable()->shouldReturn(true);
    }

    function it_is_a_product_that_the_user_can_edit_when_the_product_is_not_categorized(): void {
        $uuid = Uuid::uuid4();
        $this->beConstructedWith($uuid, 1, 0, 0, 0, 0);
        $this->isProductEditable()->shouldReturn(true);
    }

    function it_is_feasible_for_the_user_to_apply_a_draft_on_product_when_the_product_is_categorized_in_at_least_one_category_editable_and_no_category_owned_by_the_user(): void {
        $uuid = Uuid::uuid4();
        $this->beConstructedWith($uuid, 1, 1, 0, 0, 1);
        $this->canApplyDraftOnProduct()->shouldReturn(true);
    }

    function it_is_not_possible_to_edit_a_product_when_the_user_can_apply_a_draft_on_the_product(): void {
        $uuid = Uuid::uuid4();
        $this->beConstructedWith($uuid, 1, 1, 0, 0, 1);
        $this->canApplyDraftOnProduct()->shouldReturn(true);
        $this->isProductEditable()->shouldReturn(false);
    }

    function it_is_user_rights_for_a_given_product(): void {
        $uuid = Uuid::uuid4();
        $this->beConstructedWith($uuid, 1, 0, 0, 0, 0);
        $this->productUuid()->shouldReturn($uuid);
    }

    function it_is_user_rights_for_a_given_user(): void {
        $uuid = Uuid::uuid4();
        $this->beConstructedWith($uuid, 1, 0, 0, 0, 0);
        $this->userId()->shouldReturn(1);
    }
}
