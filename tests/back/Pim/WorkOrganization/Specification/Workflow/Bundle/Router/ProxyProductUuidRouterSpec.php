<?php

namespace Specification\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Router;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Router\ProxyProductUuidRouter;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ProxyProductUuidRouterSpec extends ObjectBehavior
{
    function let(
        UrlGeneratorInterface $urlGenerator,
        ProductRepositoryInterface $productRepository,
        EntityWithValuesDraftRepositoryInterface $productDraftRepository,
        TokenStorageInterface $tokenStorage
    ) {
        $this->beConstructedWith($urlGenerator, $productRepository, $productDraftRepository, $tokenStorage);
    }

    function it_is_initializable()
    {
        $this->shouldImplement(UrlGeneratorInterface::class);
        $this->shouldHaveType(ProxyProductUuidRouter::class);
    }

    function it_does_not_override_a_non_product_route(
        UrlGeneratorInterface $urlGenerator,
        ProductRepositoryInterface $productRepository
    ): void {
        $urlGenerator->generate('some_route', ['param1' => 'value1'], UrlGeneratorInterface::ABSOLUTE_PATH)
            ->shouldBeCalled()->willReturn('https://example.com');
        $productRepository->find(Argument::any())->shouldNotBeCalled();

        $this->generate('some_route', ['param1' => 'value1'])->shouldReturn('https://example.com');
    }

    function it_does_not_accept_invalid_parameters(): void
    {
        $this->shouldThrow(MissingMandatoryParametersException::class)
            ->during('generate', ['pim_api_product_uuid_get', ['foo' => 'bar']]);
    }

    function it_throws_an_exception_if_the_product_does_not_exist(
        ProductRepositoryInterface $productRepository
    ): void {
        $uuid = Uuid::uuid4()->toString();
        $productRepository->find($uuid)->shouldBeCalled()->willReturn(null);

        $this->shouldThrow(NotFoundHttpException::class)
            ->during('generate', ['pim_api_product_uuid_get', ['uuid' => $uuid]]);
    }

    function it_does_not_override_the_route_if__no_draft_exists(
        UrlGeneratorInterface $urlGenerator,
        ProductRepositoryInterface $productRepository,
        EntityWithValuesDraftRepositoryInterface $productDraftRepository,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user,
        ProductInterface $product,
    ): void {
        $uuid = Uuid::uuid4()->toString();

        $user->getUserIdentifier()->willReturn('mary');
        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);

        $productRepository->find($uuid)->shouldBeCalled()->willReturn($product);
        $productDraftRepository->findUserEntityWithValuesDraft($product, 'mary')
            ->shouldBeCalled()->willReturn(null);
        $urlGenerator->generate('pim_api_product_uuid_get', ['uuid' => $uuid], UrlGeneratorInterface::ABSOLUTE_PATH)
            ->shouldBeCalled()->willReturn('https://example.com/product_by_uuid');

        $this->generate('pim_api_product_uuid_get', ['uuid' => $uuid])->shouldReturn('https://example.com/product_by_uuid');
    }

    function it_overrides_the_route_when_a_draft_exists_for_the_user(
        UrlGeneratorInterface $urlGenerator,
        ProductRepositoryInterface $productRepository,
        EntityWithValuesDraftRepositoryInterface $productDraftRepository,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user,
        ProductInterface $product,
        EntityWithValuesDraftInterface $draft
    ): void
    {
        $uuid = Uuid::uuid4()->toString();

        $user->getUserIdentifier()->willReturn('mary');
        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);

        $productRepository->find($uuid)->shouldBeCalled()->willReturn($product);
        $productDraftRepository->findUserEntityWithValuesDraft($product, 'mary')
                               ->shouldBeCalled()->willReturn($draft);

        $urlGenerator->generate(
            'pimee_api_product_draft_get_with_uuid',
            ['uuid' => $uuid],
            UrlGeneratorInterface::ABSOLUTE_PATH
        )
                     ->shouldBeCalled()->willReturn('https://example.com/product_by_uuid/draft');

        $this->generate('pim_api_product_uuid_get', ['uuid' => $uuid])->shouldReturn(
            'https://example.com/product_by_uuid/draft'
        );
    }
}
