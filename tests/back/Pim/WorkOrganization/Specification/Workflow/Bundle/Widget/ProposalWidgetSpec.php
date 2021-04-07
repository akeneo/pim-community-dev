<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Widget;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Akeneo\Platform\Bundle\DashboardBundle\Widget\WidgetInterface;
use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
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

        $this->beConstructedWith($authorizationChecker, $productDraftRepository, $productModelDraftRepository, $tokenStorage, $presenter, $router);
    }

    function it_is_a_widget()
    {
        $this->shouldBeAnInstanceOf(WidgetInterface::class);
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
        $presenter,
        RouterInterface $router,
        ProductDraft $first,
        ProductModelDraft $second,
        ProductInterface $firstProduct,
        ProductModelInterface $secondProductModel
    ) {
        $authorizationChecker->isGranted(Attributes::OWN_AT_LEAST_ONE_CATEGORY)->willReturn(true);
        $productDraftRepository->findApprovableByUser($user, 10)->willReturn([$first]);
        $productModelDraftRepository->findApprovableByUser($user, 10)->willReturn([$second]);

        $router->generate('pim_enrich_product_edit', ['id' => 1])->willReturn('/enrich/product/1');
        $router->generate('pim_enrich_product_model_edit', ['id' => 2])->willReturn('/enrich/product-model/2');

        $first->getEntityWithValue()->willReturn($firstProduct);
        $second->getEntityWithValue()->willReturn($secondProductModel);

        $firstProduct->getId()->willReturn(1);
        $secondProductModel->getId()->willReturn(2);
        $firstProduct->getIdentifier()->willReturn('sku1');
        $secondProductModel->getCode()->willReturn('sku2');
        $firstProduct->getLabel()->willReturn('First product');
        $secondProductModel->getLabel()->willReturn('Second product');
        $first->getAuthor()->willReturn('julia');
        $first->getAuthorLabel()->willReturn('Julia Stark');
        $second->getAuthor()->willReturn('julia');
        $second->getAuthorLabel()->willReturn('Julia Stark');
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
                    'productViewUrl' => '/enrich/product/1',
                    'productReviewUrl' =>
                        '/my/route/|g/f%5Bauthor%5D%5Bvalue%5D%5B0%5D=julia&f%5Bidentifier%5D%5Bvalue%5D=sku1&f%5Bidentifier%5D%5Btype%5D=1',
                    'createdAt'        => $firstCreatedAt->format('m/d/Y')
                ],
                [
                    'productId'        => 2,
                    'productLabel'     => 'Second product',
                    'authorFullName'   => 'Julia Stark',
                    'productViewUrl' => '/enrich/product-model/2',
                    'productReviewUrl' =>
                        '/my/route/|g/f%5Bauthor%5D%5Bvalue%5D%5B0%5D=julia&f%5Bidentifier%5D%5Bvalue%5D=sku2&f%5Bidentifier%5D%5Btype%5D=1',
                    'createdAt'        => $secondCreatedAt->format('m/d/Y')
                ]
            ]
        );
    }
}
