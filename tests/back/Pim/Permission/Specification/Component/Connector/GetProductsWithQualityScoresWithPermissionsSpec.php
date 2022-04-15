<?php

namespace Specification\Akeneo\Pim\Permission\Component\Connector;

use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\Permission\GetAllViewableLocalesForUserInterface;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScore;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScoreCollection;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\GetProductsWithQualityScoresInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class GetProductsWithQualityScoresWithPermissionsSpec extends ObjectBehavior
{
    function let(
        GetProductsWithQualityScoresInterface $getProductsWithQualityScores,
        GetAllViewableLocalesForUserInterface $getAllViewableLocalesForUser,
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
        GetAllViewableLocalesForUserInterface $getAllViewableLocalesForUser
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
        GetAllViewableLocalesForUserInterface $getAllViewableLocalesForUser
    ) {
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
            $qualityScore,
            null
        );
    }

    private function buildProductWithQualityScores(ConnectorProduct $connectorProduct): ConnectorProduct
    {
        return $connectorProduct->buildWithQualityScores(
            new QualityScoreCollection([
                'ecommerce' => [
                    'de_DE' => new QualityScore('E', 15),
                    'fr_FR' => new QualityScore('E', 15),
                    'en_US' => new QualityScore('E', 15),
                ],
                'print' => [
                    'de_DE' => new QualityScore('E', 15),
                    'fr_FR' => new QualityScore('A', 91),
                ],
                'tablet' => [
                    'de_DE' => new QualityScore('D', 65),
                ],
            ])
        );
    }

    private function buildExpectedProductWithFilteredQualityScores(ConnectorProduct $connectorProduct): ConnectorProduct
    {
        return $connectorProduct->buildWithQualityScores(
            new QualityScoreCollection([
                'ecommerce' => ['fr_FR' => new QualityScore('E', 15)],
                'print' => ['fr_FR' => new QualityScore('A', 91)],
                'tablet' => [],
            ])
        );
    }
}
