<?php

namespace spec\PimEnterprise\Bundle\DashboardBundle\Widget;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProposalWidgetSpec extends ObjectBehavior
{
    function let(
        ProductDraftRepositoryInterface $repository,
        AuthorizationCheckerInterface $authorizationChecker,
        User $user,
        TokenInterface $token,
        UserManager $userManager,
        TokenStorageInterface $tokenStorage
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $this->beConstructedWith($authorizationChecker, $repository, $userManager, $tokenStorage);
    }

    function it_is_a_widget()
    {
        $this->shouldBeAnInstanceOf('Pim\Bundle\DashboardBundle\Widget\WidgetInterface');
    }

    function it_exposes_the_proposal_widget_template()
    {
        $this->getTemplate()->shouldReturn('PimEnterpriseDashboardBundle:Widget:proposal.html.twig');
    }

    function it_exposes_the_proposal_widget_template_parameters($authorizationChecker)
    {
        $authorizationChecker->isGranted(Attributes::OWN_AT_LEAST_ONE_CATEGORY)->willReturn(true);
        $this->getParameters()->shouldBeArray();
    }

    function it_hides_the_widget_if_user_is_not_the_owner_of_any_categories($authorizationChecker, $user)
    {
        $authorizationChecker->isGranted(Attributes::OWN_AT_LEAST_ONE_CATEGORY)->willReturn(false);
        $this->getParameters()->shouldReturn(['show' => false]);
        $this->getData()->shouldReturn([]);
    }

    function it_exposes_proposal_data(
        $authorizationChecker,
        $user,
        $repository,
        $userManager,
        ProductDraftInterface $first,
        ProductDraftInterface $second,
        ProductInterface $firstProduct,
        ProductInterface $secondProduct,
        User $userJulia
    ) {
        $authorizationChecker->isGranted(Attributes::OWN_AT_LEAST_ONE_CATEGORY)->willReturn(true);
        $repository->findApprovableByUser($user, 10)->willReturn([$first, $second]);

        $userJulia->getFirstName()->willReturn('Julia');
        $userJulia->getLastName()->willReturn('Stark');
        $userManager->findUserByUsername('julia')->willReturn($userJulia);

        $first->getProduct()->willReturn($firstProduct);
        $second->getProduct()->willReturn($secondProduct);

        $firstProduct->getId()->willReturn(1);
        $secondProduct->getId()->willReturn(2);
        $firstProduct->getLabel()->willReturn('First product');
        $secondProduct->getLabel()->willReturn('Second product');
        $first->getAuthor()->willReturn('julia');
        $second->getAuthor()->willReturn('julia');
        $firstCreatedAt = new \DateTime();
        $secondCreatedAt = new \DateTime();
        $first->getCreatedAt()->willReturn($firstCreatedAt);
        $second->getCreatedAt()->willReturn($secondCreatedAt);

        $this->getData()->shouldReturn(
            [
                [
                    'productId'    => 1,
                    'productLabel' => 'First product',
                    'author'       => 'Julia Stark',
                    'createdAt'    => $firstCreatedAt->format('U')
                ],
                [
                    'productId'    => 2,
                    'productLabel' => 'Second product',
                    'author'       => 'Julia Stark',
                    'createdAt'    => $secondCreatedAt->format('U')
                ]
            ]
        );
    }

    function it_fallbacks_on_username_if_user_not_found(
        $authorizationChecker,
        $user,
        $repository,
        $userManager,
        ProductDraftInterface $first,
        ProductDraftInterface $second,
        ProductInterface $firstProduct,
        ProductInterface $secondProduct
    ) {
        $authorizationChecker->isGranted(Attributes::OWN_AT_LEAST_ONE_CATEGORY)->willReturn(true);
        $repository->findApprovableByUser($user, 10)->willReturn([$first, $second]);

        $userManager->findUserByUsername('jack')->willReturn(null);

        $first->getProduct()->willReturn($firstProduct);
        $second->getProduct()->willReturn($secondProduct);

        $firstProduct->getId()->willReturn(1);
        $secondProduct->getId()->willReturn(2);
        $firstProduct->getLabel()->willReturn('First product');
        $secondProduct->getLabel()->willReturn('Second product');
        $first->getAuthor()->willReturn('jack');
        $second->getAuthor()->willReturn('jack');
        $firstCreatedAt = new \DateTime();
        $secondCreatedAt = new \DateTime();
        $first->getCreatedAt()->willReturn($firstCreatedAt);
        $second->getCreatedAt()->willReturn($secondCreatedAt);

        $this->getData()->shouldReturn(
            [
                [
                    'productId'    => 1,
                    'productLabel' => 'First product',
                    'author'       => 'jack',
                    'createdAt'    => $firstCreatedAt->format('U')
                ],
                [
                    'productId'    => 2,
                    'productLabel' => 'Second product',
                    'author'       => 'jack',
                    'createdAt'    => $secondCreatedAt->format('U')
                ]
            ]
        );
    }
}
