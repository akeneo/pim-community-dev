<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Voter;

use Pim\Bundle\UserBundle\Entity\User;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Bundle\SecurityBundle\Voter\ProductVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ProductVoterSpec extends ObjectBehavior
{
    protected $attributes = [ Attributes::VIEW, Attributes::EDIT, Attributes::OWN ];

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\SecurityBundle\Voter\ProductVoter');
    }

    function let(CategoryAccessRepository $categoryAccessRepository, TokenInterface $token, User $user)
    {
        $token->getUser()->willReturn($user);

        $this->beConstructedWith($categoryAccessRepository);
    }

    function it_supports_the_VIEW_PRODUCT_attribute()
    {
        $this->supportsAttribute(Attributes::VIEW)->shouldReturn(true);
    }

    function it_supports_the_EDIT_PRODUCT_attribute()
    {
        $this->supportsAttribute(Attributes::EDIT)->shouldReturn(true);
    }

    function it_supports_the_OWN_attribute()
    {
        $this->supportsAttribute(Attributes::OWN)->shouldReturn(true);
    }

    function it_returns_abstain_access_if_non_attribute_group_entity($token)
    {
        $this
            ->vote($token, 'foo', array('bar', 'baz'))
            ->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_returns_abstain_access_if_not_supported_entity($token, ProductVoter $wrongClass)
    {
        $this
            ->vote($token, $wrongClass, [Attributes::VIEW])
            ->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_returns_denied_access_if_user_has_no_access(
        $categoryAccessRepository,
        $token,
        $user,
        ProductInterface $product,
        CategoryInterface $categoryFive,
        CategoryInterface $categorySix
    ) {
        $categoryAccessRepository->isCategoriesGranted($user, Attributes::EDIT_PRODUCTS, [5, 6])->willReturn(false);
        $product->getCategories()->willReturn([$categoryFive, $categorySix]);
        $categoryFive->getId()->willReturn(5);
        $categorySix->getId()->willReturn(6);

        $this
            ->vote($token, $product, [Attributes::EDIT])
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
        $categoryAccessRepository->isCategoriesGranted($user, Attributes::EDIT_PRODUCTS, [1, 6])->willReturn(true);
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
        $categoryAccessRepository->isCategoriesGranted($user, Attributes::OWN_PRODUCTS, [1])->willReturn(true);

        $this->vote($token, $product, [Attributes::OWN])->shouldReturn(VoterInterface::ACCESS_GRANTED);
    }

    function it_denies_OWN_access_to_user_that_does_not_have_a_group_which_has_the_ownership_of_the_product(
        TokenInterface $token,
        ProductInterface $product,
        UserInterface $user,
        CategoryInterface $categoryOne
    ) {
        $token->getUser()->willReturn($user);

        $product->getCategories()->willReturn([$categoryOne]);
        $categoryOne->getId()->willReturn(1);

        $this->vote($token, $product, [Attributes::OWN])->shouldReturn(VoterInterface::ACCESS_DENIED);
    }

    function it_does_not_vote_if_checking_the_OWN_access_of_something_else_than_a_product(
        TokenInterface $token,
        CategoryInterface $category
    ) {
        $this->vote($token, $category, [Attributes::OWN])->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }
}
