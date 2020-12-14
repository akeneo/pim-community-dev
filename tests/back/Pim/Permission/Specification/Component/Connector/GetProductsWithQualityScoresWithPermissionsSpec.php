<?php

namespace Specification\Akeneo\Pim\Permission\Component\Connector;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\GetProductsWithQualityScoresInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\Pim\Permission\Component\Query\GetAllViewableLocalesForUser;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class GetProductsWithQualityScoresWithPermissionsSpec extends ObjectBehavior
{
    function let(
        GetProductsWithQualityScoresInterface $getProductsWithQualityScores,
        GetAllViewableLocalesForUser $getAllViewableLocalesForUser,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getId()->willReturn(1);

        $this->beConstructedWith($getProductsWithQualityScores, $getAllViewableLocalesForUser, $tokenStorage);
    }

    function it_applies_permissions_on_product_quality_scores(
        GetProductsWithQualityScoresInterface $getProductsWithQualityScores,
        GetAllViewableLocalesForUser $getAllViewableLocalesForUser
    ) {
        $getAllViewableLocalesForUser->fetchAll(1)->willReturn(['fr_FR']);

        $connectorProduct = $this->buildConnectorProduct('pdt1', null);

        $getProductsWithQualityScores->fromConnectorProduct($connectorProduct)->willReturn(
            $this->buildProductWithQualityScores($connectorProduct)
        );

        $expectedConnectorProduct = $this->buildExpectedProductWithFilteredQualityScores($connectorProduct);

        $this->fromConnectorProduct($connectorProduct)->shouldBeLike($expectedConnectorProduct);
    }

    function it_applies_permissions_on_a_list_of_product_quality_scores(
        GetProductsWithQualityScoresInterface $getProductsWithQualityScores,
        GetAllViewableLocalesForUser $getAllViewableLocalesForUser
    )
    {
        $getAllViewableLocalesForUser->fetchAll(1)->willReturn(['fr_FR']);

        $connectorProduct1 = $this->buildConnectorProduct('pdt1', null);
        $connectorProduct2 = $this->buildConnectorProduct('pdt2', null);

        $getProductsWithQualityScores->fromConnectorProductList(new ConnectorProductList(2, [$connectorProduct1, $connectorProduct2]), null, [])->willReturn(
            new ConnectorProductList(2, [
                $this->buildProductWithQualityScores($connectorProduct1),
                $this->buildProductWithQualityScores($connectorProduct2),
            ])
        );

        $expectedConnectorProduct1 = $this->buildExpectedProductWithFilteredQualityScores($connectorProduct1);
        $expectedConnectorProduct2 = $this->buildExpectedProductWithFilteredQualityScores($connectorProduct2);

        $this->fromConnectorProductList(new ConnectorProductList(2, [$connectorProduct1, $connectorProduct2]))->shouldBeLike(
            new ConnectorProductList(2, [$expectedConnectorProduct1, $expectedConnectorProduct2])
        );
    }

    private function buildConnectorProduct($identifier, $qualityScore): ConnectorProduct
    {
        return new ConnectorProduct(
            5,
            $identifier,
            new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2019-04-25 15:55:50', new \DateTimeZone('UTC')),
            true,
            'family_code',
            ['category_code_1', 'category_code_2'],
            ['group_code_1', 'group_code_2'],
            'parent_product_model_code',
            [],
            [],
            [],
            new ReadValueCollection(),
            $qualityScore
        );
    }

    private function buildProductWithQualityScores(ConnectorProduct $connectorProduct)
    {
        return $connectorProduct->buildWithQualityScores(ChannelLocaleRateCollection::fromArrayInt([
            'ecommerce' => [
                'de_DE' => 15,
                'fr_FR' => 15,
                'en_US' => 15,
            ],
            'print' => [
                'de_DE' => 15,
                'fr_FR' => 91,
            ],
            'tablet' => [
                'de_DE' => 45,
            ],
        ]));
    }

    private function buildExpectedProductWithFilteredQualityScores(ConnectorProduct $connectorProduct): ConnectorProduct
    {
        return $connectorProduct->buildWithQualityScores(ChannelLocaleRateCollection::fromArrayInt([
            'ecommerce' => [
                'fr_FR' => 15,
            ],
            'print' => [
                'fr_FR' => 91,
            ],
            'tablet' => [],
        ]));
    }
}
