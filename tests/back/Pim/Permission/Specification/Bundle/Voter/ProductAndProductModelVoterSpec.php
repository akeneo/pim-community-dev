<?php

namespace Specification\Akeneo\Pim\Permission\Bundle\Voter;

use PhpSpec\ObjectBehavior;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Permission\Bundle\Entity\Repository\CategoryAccessRepository;
use Akeneo\Pim\Permission\Bundle\Voter\ProductAndProductModelVoter;
use Akeneo\Pim\Permission\Component\Attributes;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ProductAndProductModelVoterSpec extends ObjectBehavior
{
    protected $attributes = [ Attributes::VIEW, Attributes::EDIT, Attributes::OWN ];

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductAndProductModelVoter::class);
    }

    function let(CategoryAccessRepository $categoryAccessRepository, TokenInterface $token, UserInterface $user)
    {
        $token->getUser()->willReturn($user);

        $this->beConstructedWith($categoryAccessRepository);
    }

    function it_returns_abstain_access_if_non_attribute_group_entity($token)
    {
        $this
            ->vote($token, 'foo', ['bar', 'baz'])
            ->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_returns_abstain_access_if_not_supported_entity($token, ProductAndProductModelVoter $wrongClass)
    {
        $this
            ->vote($token, $wrongClass, [Attributes::VIEW])
            ->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_returns_denied_access_if_user_has_no_access(
        $categoryAccessRepository,
        $token,
        $user,
        ProductModelInterface $productModel,
        CategoryInterface $categoryFive,
        CategoryInterface $categorySix
    ) {
        $categoryAccessRepository->isCategoryIdsGranted($user, Attributes::EDIT_ITEMS, [5, 6])->willReturn(false);
        $productModel->getCategories()->willReturn([$categoryFive, $categorySix]);
        $categoryFive->getId()->willReturn(5);
        $categorySix->getId()->willReturn(6);

        $this
            ->vote($token, $productModel, [Attributes::EDIT])
            ->shouldReturn(VoterInterface::ACCESS_DENIED);
    }

    function it_returns_granted_access_if_user_has_access(
        $categoryAccessRepository,
        $token,
        $user,
        ProductInterface $product,
        CategoryInterface $categoryOne,
        CategoryInterface $categorySix
    ) {
        $categoryAccessRepository->isCategoryIdsGranted($user, Attributes::EDIT_ITEMS, [1, 6])->willReturn(true);
        $product->getCategories()->willReturn([$categoryOne, $categorySix]);
        $categoryOne->getId()->willReturn(1);
        $categorySix->getId()->willReturn(6);

        $this
            ->vote($token, $product, [Attributes::EDIT])
            ->shouldReturn(VoterInterface::ACCESS_GRANTED);
    }

    function it_grants_OWN_access_to_user_that_has_a_group_which_has_the_ownership_of_the_product(
        $categoryAccessRepository,
        TokenInterface $token,
        ProductInterface $product,
        UserInterface $user,
        CategoryInterface $categoryOne
    ) {
        $token->getUser()->willReturn($user);
        $product->getCategories()->willReturn([$categoryOne]);
        $categoryOne->getId()->willReturn(1);
        $categoryAccessRepository->isCategoryIdsGranted($user, Attributes::OWN_PRODUCTS, [1])->willReturn(true);

        $this->vote($token, $product, [Attributes::OWN])->shouldReturn(VoterInterface::ACCESS_GRANTED);
    }

    function it_denies_OWN_access_to_user_that_does_not_have_a_group_which_has_the_ownership_of_the_product(
        $categoryAccessRepository,
        TokenInterface $token,
        ProductInterface $product,
        UserInterface $user,
        CategoryInterface $categoryOne
    ) {
        $token->getUser()->willReturn($user);
        $product->getCategories()->willReturn([$categoryOne]);
        $categoryOne->getId()->willReturn(1);
        $categoryAccessRepository->isCategoryIdsGranted($user, Attributes::OWN_PRODUCTS, [1])->willReturn(false);

        $this->vote($token, $product, [Attributes::OWN])->shouldReturn(VoterInterface::ACCESS_DENIED);
    }

    function it_does_not_vote_if_checking_the_OWN_access_of_something_else_than_a_category_aware_entity(
        TokenInterface $token,
        CategoryInterface $category
    ) {
        $this->vote($token, $category, [Attributes::OWN])->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }
}
