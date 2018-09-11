<?php

namespace spec\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Widget;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\UserManagement\Bundle\Manager\UserManager;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ProposalWidgetSpec extends ObjectBehavior
{
    function let(
        EntityWithValuesDraftRepositoryInterface $productDraftRepository,
        EntityWithValuesDraftRepositoryInterface $productModelDraftRepository,
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
        $user->getTimezone()->willReturn('Pacific/Kiritimati');
        $locale->getCode()->willReturn('en');
        $router->generate('pimee_workflow_proposal_index')->willReturn('/my/route/');

        $this->beConstructedWith($authorizationChecker, $productDraftRepository, $productModelDraftRepository, $userManager, $tokenStorage, $presenter, $router);
    }

    function it_is_a_widget()
    {
        $this->shouldBeAnInstanceOf('Akeneo\Platform\Bundle\DashboardBundle\Widget\WidgetInterface');
    }

    function it_exposes_the_proposal_widget_template()
    {
        $this->getTemplate()->shouldReturn('AkeneoPimWorkflowBundle:Proposal/Widget:proposal.html.twig');
    }

    function it_exposes_the_proposal_widget_template_parameters($authorizationChecker)
    {
        $authorizationChecker->isGranted(Attributes::OWN_AT_LEAST_ONE_CATEGORY)->willReturn(true);
        $this->getParameters()->shouldBeArray();
    }

    function it_hides_the_widget_if_user_is_not_the_owner_of_any_categories($authorizationChecker)
    {
        $authorizationChecker->isGranted(Attributes::OWN_AT_LEAST_ONE_CATEGORY)->willReturn(false);
        $this->getParameters()->shouldReturn(['show' => false]);
        $this->getData()->shouldReturn([]);
    }

    function it_exposes_proposal_data(
        $authorizationChecker,
        $user,
        $productDraftRepository,
        $productModelDraftRepository,
        $userManager,
        $presenter,
        ProductDraft $first,
        ProductModelDraft $second,
        ProductInterface $firstProduct,
        ProductModelInterface $secondProductModel,
        UserInterface $userJulia
    ) {
        $authorizationChecker->isGranted(Attributes::OWN_AT_LEAST_ONE_CATEGORY)->willReturn(true);
        $productDraftRepository->findApprovableByUser($user, 10)->willReturn([$first]);
        $productModelDraftRepository->findApprovableByUser($user, 10)->willReturn([$second]);

        $userJulia->getFirstName()->willReturn('Julia');
        $userJulia->getLastName()->willReturn('Stark');
        $userManager->findUserByUsername('julia')->willReturn($userJulia);

        $first->getEntityWithValue()->willReturn($firstProduct);
        $second->getEntityWithValue()->willReturn($secondProductModel);

        $firstProduct->getId()->willReturn(1);
        $secondProductModel->getId()->willReturn(2);
        $firstProduct->getIdentifier()->willReturn('sku1');
        $secondProductModel->getCode()->willReturn('sku2');
        $firstProduct->getLabel()->willReturn('First product');
        $secondProductModel->getLabel()->willReturn('Second product');
        $first->getAuthor()->willReturn('julia');
        $second->getAuthor()->willReturn('julia');
        $firstCreatedAt = new \DateTime();
        $secondCreatedAt = new \DateTime();
        $first->getCreatedAt()->willReturn($firstCreatedAt);
        $second->getCreatedAt()->willReturn($secondCreatedAt);

        $options = ['locale' => 'en', 'timezone' => 'Pacific/Kiritimati'];
        $presenter->present($firstCreatedAt, $options)->willReturn($firstCreatedAt->format('m/d/Y'));
        $presenter->present($secondCreatedAt, $options)->willReturn($secondCreatedAt->format('m/d/Y'));

        $this->getData()->shouldReturn(
            [
                [
                    'productId'        => 1,
                    'productLabel'     => 'First product',
                    'authorFullName'   => 'Julia Stark',
                    'productReviewUrl' =>
                        '/my/route/|g/f%5Bauthor%5D%5Bvalue%5D%5B0%5D=julia&f%5Bidentifier%5D%5Bvalue%5D=sku1&f%5Bidentifier%5D%5Btype%5D=1',
                    'createdAt'        => $firstCreatedAt->format('m/d/Y')
                ],
                [
                    'productId'        => 2,
                    'productLabel'     => 'Second product',
                    'authorFullName'   => 'Julia Stark',
                    'productReviewUrl' =>
                        '/my/route/|g/f%5Bauthor%5D%5Bvalue%5D%5B0%5D=julia&f%5Bidentifier%5D%5Bvalue%5D=sku2&f%5Bidentifier%5D%5Btype%5D=1',
                    'createdAt'        => $secondCreatedAt->format('m/d/Y')
                ]
            ]
        );
    }

    function it_fallbacks_on_username_if_user_not_found(
        $authorizationChecker,
        $user,
        $productDraftRepository,
        $productModelDraftRepository,
        $userManager,
        $presenter,
        ProductDraft $first,
        ProductDraft $second,
        ProductInterface $firstProduct,
        ProductInterface $secondProduct
    ) {
        $authorizationChecker->isGranted(Attributes::OWN_AT_LEAST_ONE_CATEGORY)->willReturn(true);
        $productDraftRepository->findApprovableByUser($user, 10)->willReturn([$first, $second]);
        $productModelDraftRepository->findApprovableByUser($user, 10)->willReturn([]);

        $userManager->findUserByUsername('jack')->willReturn(null);

        $first->getEntityWithValue()->willReturn($firstProduct);
        $second->getEntityWithValue()->willReturn($secondProduct);

        $firstProduct->getId()->willReturn(1);
        $secondProduct->getId()->willReturn(2);
        $firstProduct->getIdentifier()->willReturn('sku1');
        $secondProduct->getIdentifier()->willReturn('sku2');
        $firstProduct->getLabel()->willReturn('First product');
        $secondProduct->getLabel()->willReturn('Second product');
        $first->getAuthor()->willReturn('jack');
        $second->getAuthor()->willReturn('jack');
        $firstCreatedAt = new \DateTime();
        $secondCreatedAt = new \DateTime();
        $first->getCreatedAt()->willReturn($firstCreatedAt);
        $second->getCreatedAt()->willReturn($secondCreatedAt);

        $options = ['locale' => 'en', 'timezone' => 'Pacific/Kiritimati'];
        $presenter->present($firstCreatedAt, $options)->willReturn($firstCreatedAt->format('m/d/Y'));
        $presenter->present($secondCreatedAt, $options)->willReturn($secondCreatedAt->format('m/d/Y'));

        $this->getData()->shouldReturn(
            [
                [
                    'productId'        => 1,
                    'productLabel'     => 'First product',
                    'authorFullName'   => 'jack',
                    'productReviewUrl' =>
                        '/my/route/|g/f%5Bauthor%5D%5Bvalue%5D%5B0%5D=jack&f%5Bidentifier%5D%5Bvalue%5D=sku1&f%5Bidentifier%5D%5Btype%5D=1',
                    'createdAt'        => $firstCreatedAt->format('m/d/Y')
                ],
                [
                    'productId'        => 2,
                    'productLabel'     => 'Second product',
                    'authorFullName'   => 'jack',
                    'productReviewUrl' =>
                        '/my/route/|g/f%5Bauthor%5D%5Bvalue%5D%5B0%5D=jack&f%5Bidentifier%5D%5Bvalue%5D=sku2&f%5Bidentifier%5D%5Btype%5D=1',
                    'createdAt'        => $secondCreatedAt->format('m/d/Y')
                ]
            ]
        );
    }
}
