<?php

namespace spec\PimEnterprise\Bundle\DashboardBundle\Widget;

use Akeneo\Component\Localization\Presenter\PresenterInterface;
use Oro\Bundle\UserBundle\Entity\UserManager;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\UserBundle\Entity\UserInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Workflow\Model\ProductDraftInterface;
use PimEnterprise\Component\Workflow\Repository\ProductDraftRepositoryInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProposalWidgetSpec extends ObjectBehavior
{
    function let(
        ProductDraftRepositoryInterface $repository,
        AuthorizationCheckerInterface $authorizationChecker,
        UserInterface $user,
        TokenInterface $token,
        UserManager $userManager,
        TokenStorageInterface $tokenStorage,
        PresenterInterface $presenter,
        LocaleInterface $locale,
        RouterInterface $router
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $user->getUiLocale()->willReturn($locale);
        $locale->getCode()->willReturn('en');
        $router->generate('pimee_workflow_proposal_index')->willReturn('/my/route/');

        $this->beConstructedWith($authorizationChecker, $repository, $userManager, $tokenStorage, $presenter, $router);
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
        $presenter,
        ProductDraftInterface $first,
        ProductDraftInterface $second,
        ProductInterface $firstProduct,
        ProductInterface $secondProduct,
        UserInterface $userJulia
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

        $options = ['locale' => 'en'];
        $presenter->present($firstCreatedAt, $options)->willReturn($firstCreatedAt->format('m/d/Y'));
        $presenter->present($secondCreatedAt, $options)->willReturn($secondCreatedAt->format('m/d/Y'));

        $this->getData()->shouldReturn(
            [
                [
                    'productId'        => 1,
                    'productLabel'     => 'First product',
                    'authorFullName'   => 'Julia Stark',
                    'productReviewUrl' =>
                        '/my/route/|g/f%5Bauthor%5D%5Bvalue%5D%5B0%5D=julia&f%5Bproduct%5D%5Bvalue%5D%5B0%5D=1',
                    'createdAt'        => $firstCreatedAt->format('m/d/Y')
                ],
                [
                    'productId'        => 2,
                    'productLabel'     => 'Second product',
                    'authorFullName'   => 'Julia Stark',
                    'productReviewUrl' =>
                        '/my/route/|g/f%5Bauthor%5D%5Bvalue%5D%5B0%5D=julia&f%5Bproduct%5D%5Bvalue%5D%5B0%5D=2',
                    'createdAt'        => $secondCreatedAt->format('m/d/Y')
                ]
            ]
        );
    }

    function it_fallbacks_on_username_if_user_not_found(
        $authorizationChecker,
        $user,
        $repository,
        $userManager,
        $presenter,
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

        $options = ['locale' => 'en'];
        $presenter->present($firstCreatedAt, $options)->willReturn($firstCreatedAt->format('m/d/Y'));
        $presenter->present($secondCreatedAt, $options)->willReturn($secondCreatedAt->format('m/d/Y'));

        $this->getData()->shouldReturn(
            [
                [
                    'productId'        => 1,
                    'productLabel'     => 'First product',
                    'authorFullName'   => 'jack',
                    'productReviewUrl' =>
                        '/my/route/|g/f%5Bauthor%5D%5Bvalue%5D%5B0%5D=jack&f%5Bproduct%5D%5Bvalue%5D%5B0%5D=1',
                    'createdAt'        => $firstCreatedAt->format('m/d/Y')
                ],
                [
                    'productId'        => 2,
                    'productLabel'     => 'Second product',
                    'authorFullName'   => 'jack',
                    'productReviewUrl' =>
                        '/my/route/|g/f%5Bauthor%5D%5Bvalue%5D%5B0%5D=jack&f%5Bproduct%5D%5Bvalue%5D%5B0%5D=2',
                    'createdAt'        => $secondCreatedAt->format('m/d/Y')
                ]
            ]
        );
    }
}
