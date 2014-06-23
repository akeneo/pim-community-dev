<?php

namespace spec\PimEnterprise\Bundle\SecurityBundle\Voter;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Oro\Bundle\UserBundle\Entity\Role;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryOwnershipRepository;

class ProductOwnershipVoterSpec extends ObjectBehavior
{
    function let(CategoryOwnershipRepository $repository)
    {
        $this->beConstructedWith($repository);
    }

    function it_is_a_security_voter()
    {
        $this->shouldBeAnInstanceOf('Symfony\Component\Security\Core\Authorization\Voter\VoterInterface');
    }

    function it_supports_the_OWNER_attribute()
    {
        $this->supportsAttribute('OWNER')->shouldReturn(true);
    }

    function it_supports_product(ProductInterface $product)
    {
        $this->supportsClass($product)->shouldReturn(true);
    }

    function it_grants_OWNER_access_to_user_that_has_a_role_which_has_the_ownership_of_the_product(
        $repository,
        TokenInterface $token,
        ProductInterface $product,
        UserInterface $user,
        Role $manager
    ) {
        $token->getUser()->willReturn($user);
        $user->getRoles()->willReturn([$manager]);
        $manager->getId()->willReturn(10);
        $repository->findRolesForProduct($product)->willReturn([['id' => 10], ['id' => 14]]);

        $this->vote($token, $product, ['OWNER'])->shouldReturn(VoterInterface::ACCESS_GRANTED);
    }

    function it_denies_OWNER_access_to_user_that_does_not_have_a_role_which_has_the_ownership_of_the_product(
        $repository,
        TokenInterface $token,
        ProductInterface $product,
        UserInterface $user,
        Role $manager
    ) {
        $token->getUser()->willReturn($user);
        $user->getRoles()->willReturn([$manager]);
        $manager->getId()->willReturn(10);
        $repository->findRolesForProduct($product)->willReturn([['id' => 14]]);

        $this->vote($token, $product, ['OWNER'])->shouldReturn(VoterInterface::ACCESS_DENIED);
    }

    function it_does_not_vote_if_the_attribute_OWNER_is_not_being_checked(
        TokenInterface $token,
        ProductInterface $product
    ) {
        $this->vote($token, $product, ['CONTRIBUTOR'])->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }

    function it_does_not_vote_if_checking_the_OWNER_access_of_something_else_than_a_product(
        TokenInterface $token,
        CategoryInterface $category
    ) {
        $this->vote($token, $category, ['OWNER'])->shouldReturn(VoterInterface::ACCESS_ABSTAIN);
    }
}
