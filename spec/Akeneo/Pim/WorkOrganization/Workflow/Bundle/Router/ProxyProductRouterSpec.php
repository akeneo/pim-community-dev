<?php

namespace spec\Akeneo\Pim\WorkOrganization\Workflow\Bundle\Router;

use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ExternalApi\ProductRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Router\ProxyProductRouter;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\EntityWithValuesDraftRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ProxyProductRouterSpec extends ObjectBehavior
{
    function let(
        UrlGeneratorInterface $router,
        TokenStorageInterface $tokenStorage,
        EntityWithValuesDraftRepositoryInterface $productDraftRepository,
        ProductRepositoryInterface $productRepository
    ) {
        $this->beConstructedWith(
            $router,
            $tokenStorage,
            $productDraftRepository,
            $productRepository,
            'pimee_product_draft_get'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProxyProductRouter::class);
    }

    function it_should_be_a_query_param_checker()
    {
        $this->shouldHaveType(UrlGeneratorInterface::class);
    }

    function it_generates_a_product_route(
        $router,
        $productRepository,
        $productDraftRepository,
        $tokenStorage,
        ProductInterface $product,
        TokenInterface $token,
        UserInterface $user
    ) {
        $name = 'pim_product_get';
        $parameters = ['code' => 'my_product'];
        $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH;

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getUsername()->willReturn('mary');

        $productRepository->findOneByIdentifier('my_product')->willReturn($product);
        $productDraftRepository->findUserEntityWithValuesDraft($product, 'mary')->willReturn(null);

        $router->generate($name, $parameters, $referenceType)->willReturn('http://localhost/api/products/my_product');

        $this->generate($name, $parameters, $referenceType)->shouldReturn('http://localhost/api/products/my_product');
    }

    function it_generates_a_product_draft_route(
        $router,
        $productRepository,
        $productDraftRepository,
        $tokenStorage,
        ProductInterface $product,
        TokenInterface $token,
        UserInterface $user,
        EntityWithValuesDraftInterface $productDraft
    ) {
        $name = 'pim_product_get';
        $parameters = ['code' => 'my_product'];
        $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH;

        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getUsername()->willReturn('mary');
        $productRepository->findOneByIdentifier('my_product')->willReturn($product);
        $productDraftRepository->findUserEntityWithValuesDraft($product, 'mary')->willReturn($productDraft);

        $router->generate('pimee_product_draft_get', $parameters, $referenceType)->willReturn('http://localhost/api/products/my_product/draft');

        $this->generate($name, $parameters, $referenceType)->shouldReturn('http://localhost/api/products/my_product/draft');
    }

    function it_throws_an_exception_if_code_is_missing()
    {
        $this->shouldThrow(
            new MissingMandatoryParametersException('Parameter "code" is missing in the parameters')
        )->during('generate', ['pim_product_get', [], UrlGeneratorInterface::ABSOLUTE_PATH]);
    }

    function it_throws_an_exception_if_product_does_not_exist($productRepository)
    {
        $productRepository->findOneByIdentifier('my_product')->willReturn(null);

        $this->shouldThrow(
            new NotFoundHttpException('Product "my_product" does not exist')
        )->during('generate', ['pim_product_get', ['code' => 'my_product'], UrlGeneratorInterface::ABSOLUTE_PATH]);
    }
}
