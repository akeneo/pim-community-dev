<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Permission\Component\Connector;

use Akeneo\Channel\Component\Query\PublicApi\Permission\GetAllViewableLocalesForUserInterface;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\GetProductsWithCompletenessesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Permission\Component\Connector\GetProductsWithCompletenessesWithPermissions;
use Akeneo\UserManagement\Component\Model\User;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class GetProductsWithCompletenessesWithPermissionsSpec extends ObjectBehavior
{
    public function let(
        GetProductsWithCompletenessesInterface $getProductsWithCompletenesses,
        GetAllViewableLocalesForUserInterface $getAllViewableLocalesForUser,
        TokenStorageInterface $tokenStorage
    ): void {
        $this->beConstructedWith(
            $getProductsWithCompletenesses,
            $getAllViewableLocalesForUser,
            $tokenStorage
        );
    }

    public function it_is_a_get_products_with_completenesses_with_permission(): void
    {
        $this->shouldHaveType(GetProductsWithCompletenessesWithPermissions::class);
        $this->shouldImplement(GetProductsWithCompletenessesInterface::class);
    }

    public function it_applies_permission_when_adding_completenesses_to_a_connector_product(
        GetProductsWithCompletenessesInterface $getProductsWithCompletenesses,
        GetAllViewableLocalesForUserInterface $getAllViewableLocalesForUser,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token
    ): void {
        $user = (new User())->setId(42);
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $viewableLocales = ['en_US', 'fr_FR'];
        $getAllViewableLocalesForUser->fetchAll(42)->willReturn($viewableLocales);

        $ecommerceUS = new ProductCompleteness('ecommerce', 'en_US', 10, 5);
        $ecommerceFR = new ProductCompleteness('ecommerce', 'fr_FR', 10, 1);
        $printDE = new ProductCompleteness('print', 'de_DE', 4, 0);
        $completenessesWithoutPermissions = [
            $ecommerceUS,
            $ecommerceFR,
            $printDE,
        ];
        $connectProduct = $this->getConnectorProduct(42);
        $connectProductCompletenesses = $this->getConnectorProduct(
            42,
            new ProductCompletenessCollection(42, $completenessesWithoutPermissions)
        );
        $connectProductCompletenessesWithPermissions = $this->getConnectorProduct(
            42,
            new ProductCompletenessCollection(42, [$ecommerceUS, $ecommerceFR])
        );
        $getProductsWithCompletenesses->fromConnectorProduct($connectProduct)->willReturn($connectProductCompletenesses);

        $this->fromConnectorProduct($connectProduct)->shouldBeLike($connectProductCompletenessesWithPermissions);
    }

    public function it_returns_product_even_if_there_is_no_permission_to_apply(
        GetProductsWithCompletenessesInterface $getProductsWithCompletenesses,
        GetAllViewableLocalesForUserInterface $getAllViewableLocalesForUser,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token
    ): void {
        $user = (new User())->setId(42);
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $viewableLocales = ['en_US', 'fr_FR'];
        $getAllViewableLocalesForUser->fetchAll(42)->willReturn($viewableLocales);

        $connectProduct = $this->getConnectorProduct(42);
        $productWithEmptyCollection = $this->getConnectorProduct(
            42,
            new ProductCompletenessCollection(42, [])
        );
        $getProductsWithCompletenesses->fromConnectorProduct($connectProduct)->willReturn($productWithEmptyCollection);

        $this->fromConnectorProduct($connectProduct)->shouldBeLike($productWithEmptyCollection);
    }

    public function it_applies_permission_when_adding_completenesses_to_a_connector_product_list(
        GetProductsWithCompletenessesInterface $getProductsWithCompletenesses,
        GetAllViewableLocalesForUserInterface $getAllViewableLocalesForUser,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token
    ): void {
        $user = (new User())->setId(42);
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $viewableLocales = ['en_US', 'de_DE'];
        $getAllViewableLocalesForUser->fetchAll(42)->willReturn($viewableLocales);

        $ecommerceUSHalf = new ProductCompleteness('ecommerce', 'en_US', 10, 5);
        $ecommerceUSComplete = new ProductCompleteness('ecommerce', 'en_US', 10, 0);
        $connectorProductList = new ConnectorProductList(
            2,
            [$this->getConnectorProduct(15), $this->getConnectorProduct(42)]
        );
        $listWithCompletenesses = new ConnectorProductList(
            2,
            [
                $this->getConnectorProduct(15, new ProductCompletenessCollection(15, [$ecommerceUSHalf])),
                $this->getConnectorProduct(42, new ProductCompletenessCollection(15, [$ecommerceUSComplete])),
            ]
        );
        $getProductsWithCompletenesses
            ->fromConnectorProductList($connectorProductList, 'ecommerce', ['en_US'])
            ->shouldBeCalled()
            ->willReturn($listWithCompletenesses);

        $this->fromConnectorProductList($connectorProductList, 'ecommerce', ['en_US', 'fr_FR'])->shouldReturn($listWithCompletenesses);
    }

    public function it_applies_permission_when_adding_completenesses_to_a_connector_product_list_and_without_locale_filter(
        GetProductsWithCompletenessesInterface $getProductsWithCompletenesses,
        GetAllViewableLocalesForUserInterface $getAllViewableLocalesForUser,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token
    ): void {
        $user = (new User())->setId(42);
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $viewableLocales = ['en_US'];
        $getAllViewableLocalesForUser->fetchAll(42)->willReturn($viewableLocales);

        $ecommerceUS = new ProductCompleteness('ecommerce', 'en_US', 10, 5);
        $printUS = new ProductCompleteness('print', 'en_US', 10, 0);
        $connectorProductList = new ConnectorProductList(
            2,
            [$this->getConnectorProduct(15), $this->getConnectorProduct(42)]
        );
        $listWithCompletenesses = new ConnectorProductList(
            2,
            [
                $this->getConnectorProduct(15, new ProductCompletenessCollection(15, [$ecommerceUS])),
                $this->getConnectorProduct(42, new ProductCompletenessCollection(15, [$printUS])),
            ]
        );
        $getProductsWithCompletenesses
            ->fromConnectorProductList($connectorProductList, null, ['en_US'])
            ->shouldBeCalled()
            ->willReturn($listWithCompletenesses);

        $this->fromConnectorProductList($connectorProductList)->shouldReturn($listWithCompletenesses);
    }

    public function it_applies_permission_when_adding_completenesses_to_a_connector_product_list_but_no_locale_viewable(
        GetProductsWithCompletenessesInterface $getProductsWithCompletenesses,
        GetAllViewableLocalesForUserInterface $getAllViewableLocalesForUser,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token
    ): void {
        $user = (new User())->setId(42);
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);

        $viewableLocales = [];
        $getAllViewableLocalesForUser->fetchAll(42)->willReturn($viewableLocales);

        $ecommerceUS = new ProductCompleteness('ecommerce', 'en_US', 10, 5);
        $printUS = new ProductCompleteness('print', 'en_US', 10, 0);
        $connectorProductList = new ConnectorProductList(
            2,
            [$this->getConnectorProduct(15), $this->getConnectorProduct(42)]
        );
        $listWithCompletenesses = new ConnectorProductList(
            2,
            [
                $this->getConnectorProduct(15, new ProductCompletenessCollection(15, [$ecommerceUS])),
                $this->getConnectorProduct(42, new ProductCompletenessCollection(15, [$printUS])),
            ]
        );
        $getProductsWithCompletenesses
            ->fromConnectorProductList($connectorProductList, null, $viewableLocales)
            ->shouldBeCalled()
            ->willReturn($listWithCompletenesses);

        $this->fromConnectorProductList($connectorProductList, null, ['en_US', 'fr_FR'])->shouldReturn($listWithCompletenesses);
    }

    public function it_throws_an_exception_if_there_is_no_user_connected(TokenStorageInterface $tokenStorage): void
    {
        $tokenStorage->getToken()->willReturn(null);

        $this
            ->shouldThrow(
                new \DomainException('A user must be connected to apply permissions.')
            )
            ->during('fromConnectorProduct', [$this->getConnectorProduct(2)]);
    }

    private function getConnectorProduct(int $id, ProductCompletenessCollection $collection = null): ConnectorProduct
    {
        return new ConnectorProduct(
            $id,
            'blue_jean',
            new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2019-04-25 15:55:50', new \DateTimeZone('UTC')),
            true,
            'jeans',
            ['category_code_1', 'category_code_2'],
            ['group_code_1', 'group_code_2'],
            null,
            [],
            [],
            [],
            new ReadValueCollection(),
            null,
            $collection
        );
    }
}
