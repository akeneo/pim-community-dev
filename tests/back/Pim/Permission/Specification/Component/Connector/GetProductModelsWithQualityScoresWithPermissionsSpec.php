<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Permission\Component\Connector;

use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\Permission\GetAllViewableLocalesForUserInterface;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScore;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScoreCollection;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModelList;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\GetProductModelsWithQualityScoresInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ReadValueCollection;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

final class GetProductModelsWithQualityScoresWithPermissionsSpec extends ObjectBehavior
{
    public function let(
        GetProductModelsWithQualityScoresInterface $getProductModelsWithQualityScores,
        GetAllViewableLocalesForUserInterface $getAllViewableLocalesForUser,
        TokenStorageInterface $tokenStorage,
        TokenInterface $token,
        UserInterface $user
    ) {
        $tokenStorage->getToken()->willReturn($token);
        $token->getUser()->willReturn($user);
        $user->getId()->willReturn(1);

        $this->beConstructedWith($getProductModelsWithQualityScores, $getAllViewableLocalesForUser, $tokenStorage);
    }

    public function it_applies_permissions_on_a_product_model_with_quality_scores(
        $getProductModelsWithQualityScores,
        $getAllViewableLocalesForUser
    ) {
        $getAllViewableLocalesForUser->fetchAll(1)->willReturn(['fr_FR']);

        $connectorProductModel = $this->givenAProductModelWithoutQualityScores('whatever');

        $getProductModelsWithQualityScores->fromConnectorProductModel($connectorProductModel)->willReturn(
            $connectorProductModel->buildWithQualityScores($this->givenQualityScoresOnAllLocales())
        );

        $expectedConnectorProductModel = $connectorProductModel->buildWithQualityScores(
            $this->givenQualityScoresFilteredOnLocale()
        );

        $this->fromConnectorProductModel($connectorProductModel)->shouldBeLike($expectedConnectorProductModel);
    }

    public function it_applies_permissions_on_a_list_of_product_models_with_quality_scores(
        $getProductModelsWithQualityScores,
        $getAllViewableLocalesForUser
    ) {
        $getAllViewableLocalesForUser->fetchAll(1)->willReturn(['fr_FR']);

        $connectorProductModel1 = $this->givenAProductModelWithoutQualityScores('pm_1');
        $connectorProductModel2 = $this->givenAProductModelWithoutQualityScores('pm_2');
        $connectorProductModelList = new ConnectorProductModelList(2, [$connectorProductModel1, $connectorProductModel2]);

        $getProductModelsWithQualityScores->fromConnectorProductModelList($connectorProductModelList, null, [])->willReturn(
            new ConnectorProductModelList(2,[
                $connectorProductModel1->buildWithQualityScores($this->givenQualityScoresOnAllLocales()),
                $connectorProductModel2->buildWithQualityScores($this->givenQualityScoresOnAllLocales()),
            ]),
        );

        $expectedConnectorProductModelList = new ConnectorProductModelList(2,[
            $connectorProductModel1->buildWithQualityScores($this->givenQualityScoresFilteredOnLocale()),
            $connectorProductModel2->buildWithQualityScores($this->givenQualityScoresFilteredOnLocale()),
        ]);

        $this->fromConnectorProductModelList($connectorProductModelList)->shouldBeLike($expectedConnectorProductModelList);
    }

    private function givenAProductModelWithoutQualityScores(string $code): ConnectorProductModel
    {
        return new ConnectorProductModel(
            1234,
            $code,
            new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
            new \DateTimeImmutable('2019-04-23 15:55:50', new \DateTimeZone('UTC')),
            'my_parent',
            'my_family',
            'my_family_variant',
            ['workflow_status' => 'working_copy'],
            [],
            [],
            ['category_code_1'],
            new ReadValueCollection(),
            null
        );
    }

    private function givenQualityScoresOnAllLocales(): QualityScoreCollection
    {
        return new QualityScoreCollection([
            'ecommerce' => [
                'de_DE' => new QualityScore('D', 65),
                'fr_FR' => new QualityScore('E', 35),
                'en_US' => new QualityScore('C', 75),
            ],
            'print' => [
                'fr_FR' => new QualityScore('A', 91),
            ],
        ]);
    }

    private function givenQualityScoresFilteredOnLocale(): QualityScoreCollection
    {
        return new QualityScoreCollection([
            'ecommerce' => [
                'fr_FR' => new QualityScore('E', 35),
            ],
            'print' => [
                'fr_FR' => new QualityScore('A', 91),
            ],
        ]);
    }
}
