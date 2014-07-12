<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Voter;

use Oro\Bundle\UserBundle\Entity\User;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AbstractProduct;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Voter\ProductVoter;
use Prophecy\Argument;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Oro\Bundle\UserBundle\Entity\Role;

class ProductVoterSpec extends ObjectBehavior
{
    protected $attributes = [ Attributes::VIEW_PRODUCT, Attributes::EDIT_PRODUCT, Attributes::OWNER ];

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
        $this->supportsAttribute(Attributes::VIEW_PRODUCT)->shouldReturn(true);
    }

    function it_supports_the_EDIT_PRODUCT_attribute()
    {
        $this->supportsAttribute(Attributes::EDIT_PRODUCT)->shouldReturn(true);
    }

    function it_supports_the_OWNER_attribute()
    {
        $this->supportsAttribute(Attributes::OWNER)->shouldReturn(true);
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
            ->vote($token, $wrongClass, [Attributes::VIEW_PRODUCT])
            ->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_returns_denied_access_if_user_has_no_access(
        $categoryAccessRepository,
        $token,
        $user,
        AbstractProduct $product,
        CategoryInterface $categoryFive,
        CategoryInterface $categorySix
    ) {
        $categoryAccessRepository->getGrantedCategoryIds($user, Attributes::EDIT_PRODUCTS)->willReturn([1, 3]);
        $product->getCategories()->willReturn([$categoryFive, $categorySix]);
        $categoryFive->getId()->willReturn(5);
        $categorySix->getId()->willReturn(6);

        $this
            ->vote($token, $product, [Attributes::EDIT_PRODUCT])
            ->shouldReturn(VoterInterface::ACCESS_DENIED);
    }

    function it_returns_granted_access_if_user_has_access(
        $categoryAccessRepository,
        $token,
        $user,
        AbstractProduct $product,
        CategoryInterface $categoryOne,
        CategoryInterface $categorySix
    ) {
        $categoryAccessRepository->getGrantedCategoryIds($user, Attributes::EDIT_PRODUCTS)->willReturn([1, 3]);
        $product->getCategories()->willReturn([$categoryOne, $categorySix]);
        $categoryOne->getId()->willReturn(1);
        $categorySix->getId()->willReturn(6);

        $this
            ->vote($token, $product, [Attributes::EDIT_PRODUCT])
            ->shouldReturn(VoterInterface::ACCESS_GRANTED);
    }

    function it_grants_OWNER_access_to_user_that_has_a_role_which_has_the_ownership_of_the_product(
        $categoryAccessRepository,
        TokenInterface $token,
        ProductInterface $product,
        UserInterface $user,
        CategoryInterface $categoryOne
    ) {
        $token->getUser()->willReturn($user);

        $product->getCategories()->willReturn([$categoryOne]);
        $categoryOne->getId()->willReturn(1);
        $categoryAccessRepository->getGrantedCategoryIds($user, Attributes::OWN_PRODUCTS)->willReturn([1]);

        $this->vote($token, $product, ['OWNER'])->shouldReturn(VoterInterface::ACCESS_GRANTED);
    }

    function it_denies_OWNER_access_to_user_that_does_not_have_a_role_which_has_the_ownership_of_the_product(
        $categoryAccessRepository,
        TokenInterface $token,
        ProductInterface $product,
        UserInterface $user,
        CategoryInterface $categoryOne
    ) {
        $token->getUser()->willReturn($user);

        $product->getCategories()->willReturn([$categoryOne]);
        $categoryOne->getId()->willReturn(1);
        $categoryAccessRepository->getGrantedCategoryIds($user, Attributes::OWN_PRODUCTS)->willReturn([2, 3]);

        $this->vote($token, $product, ['OWNER'])->shouldReturn(VoterInterface::ACCESS_DENIED);
    }

    function it_does_not_vote_if_checking_the_OWNER_access_of_something_else_than_a_product(
        TokenInterface $token,
        CategoryInterface $category
    ) {
        $this->vote($token, $category, ['OWNER'])->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }
}
