<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Widget;

use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
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
use Ramsey\Uuid\Uuid;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
        $this->getTemplate()->shouldReturn('');
    }

    function it_exposes_the_proposal_widget_template_parameters($authorizationChecker)
    {
        $authorizationChecker->isGranted(Attributes::OWN_AT_LEAST_ONE_CATEGORY)->willReturn(true);
        $this->getParameters()->shouldBeArray();
    }

    function it_throws_an_exception_if_the_user_is_not_the_owner_of_any_categories($authorizationChecker)
    {
        $authorizationChecker->isGranted(Attributes::OWN_AT_LEAST_ONE_CATEGORY)->willReturn(false);
        $this->getParameters()->shouldReturn(['show' => false]);

        $this->shouldThrow(AccessDeniedException::class)->during('getData', []);
    }

    function it_exposes_proposal_data(
        $authorizationChecker,
        $user,
        $productDraftRepository,
        $productModelDraftRepository,
        $presenter,
        $router,
        ProductDraft $productDraft,
        ProductModelDraft $productModelDraft,
        ProductInterface $product,
        ProductModelInterface $productModel
    ) {
        $authorizationChecker->isGranted(Attributes::OWN_AT_LEAST_ONE_CATEGORY)->willReturn(true);
        $productDraftRepository->findApprovableByUser($user, 10)->willReturn([$productDraft]);
        $productModelDraftRepository->findApprovableByUser($user, 10)->willReturn([$productModelDraft]);

        $productDraft->getEntityWithValue()->willReturn($product);
        $productModelDraft->getEntityWithValue()->willReturn($productModel);

        $product->getUuid()->willReturn(Uuid::fromString('df470d52-7723-4890-85a0-e79be625e2ed'));
        $productModel->getId()->willReturn(2);
        $product->getIdentifier()->willReturn('sku1');
        $productModel->getCode()->willReturn('sku2');
        $product->getLabel()->willReturn('First product');
        $productModel->getLabel()->willReturn('Second product');
        $productDraft->getAuthor()->willReturn('julia');
        $productDraft->getAuthorLabel()->willReturn('Julia Stark');
        $productModelDraft->getAuthor()->willReturn('julia');
        $productModelDraft->getAuthorLabel()->willReturn('Julia Stark');
        $firstCreatedAt = new \DateTime();
        $secondCreatedAt = new \DateTime();
        $productDraft->getCreatedAt()->willReturn($firstCreatedAt);
        $productModelDraft->getCreatedAt()->willReturn($secondCreatedAt);

        $options = ['locale' => 'en', 'timezone' => 'Pacific/Kiritimati'];
        $presenter->present($firstCreatedAt, $options)->willReturn($firstCreatedAt->format('m/d/Y'));
        $presenter->present($secondCreatedAt, $options)->willReturn($secondCreatedAt->format('m/d/Y'));

        $router->generate('pim_enrich_product_edit', ['uuid' => 'df470d52-7723-4890-85a0-e79be625e2ed'])->willReturn('/enrich/product/df470d52-7723-4890-85a0-e79be625e2ed');
        $router->generate('pim_enrich_product_model_edit', ['id' => 2])->willReturn('/enrich/product_model/2');

        $this->getData()->shouldReturn(
            [
                [
                    'productId'        => 'df470d52-7723-4890-85a0-e79be625e2ed',
                    'productLabel'     => 'First product',
                    'authorFullName'   => 'Julia Stark',
                    'productEditUrl'   => '/enrich/product/df470d52-7723-4890-85a0-e79be625e2ed',
                    'productReviewUrl' =>
                        '/my/route/|g/f%5Bauthor%5D%5Bvalue%5D%5B0%5D=julia&f%5Bidentifier%5D%5Bvalue%5D=sku1&f%5Bidentifier%5D%5Btype%5D=1',
                    'createdAt'        => $firstCreatedAt->format('m/d/Y')
                ],
                [
                    'productId'        => 2,
                    'productLabel'     => 'Second product',
                    'authorFullName'   => 'Julia Stark',
                    'productEditUrl'   => '/enrich/product_model/2',
                    'productReviewUrl' =>
                        '/my/route/|g/f%5Bauthor%5D%5Bvalue%5D%5B0%5D=julia&f%5Bidentifier%5D%5Bvalue%5D=sku2&f%5Bidentifier%5D%5Btype%5D=1',
                    'createdAt'        => $secondCreatedAt->format('m/d/Y')
                ]
            ]
        );
    }
}
