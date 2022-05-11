<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Component\Connector;

use Akeneo\Channel\Infrastructure\Component\Query\PublicApi\Permission\GetAllViewableLocalesForUserInterface;
use Akeneo\Pim\Automation\DataQualityInsights\PublicApi\Model\QualityScoreCollection;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModel;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductModelList;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\GetProductModelsWithQualityScoresInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Webmozart\Assert\Assert;

final class GetProductModelsWithQualityScoresWithPermissions implements GetProductModelsWithQualityScoresInterface
{
    public function __construct(
        private GetProductModelsWithQualityScoresInterface $getProductModelsWithQualityScores,
        private GetAllViewableLocalesForUserInterface $getAllViewableLocalesForUser,
        private TokenStorageInterface $tokenStorage,
    ) {
    }

    public function fromConnectorProductModel(ConnectorProductModel $productModel): ConnectorProductModel
    {
        $productModelWithQualityScore = $this->getProductModelsWithQualityScores->fromConnectorProductModel($productModel);

        $filteredQualityScores = $this->filterQualityScoresByGrantedLocales($productModelWithQualityScore->qualityScores());

        return $productModel->buildWithQualityScores($filteredQualityScores);
    }

    public function fromConnectorProductModelList(ConnectorProductModelList $connectorProductModelList, ?string $channel = null, array $locales = []): ConnectorProductModelList
    {
        $productModels = $this->getProductModelsWithQualityScores->fromConnectorProductModelList($connectorProductModelList, $channel, $locales);

        $filteredProductModels = array_map(function (ConnectorProductModel $productModelWithQualityScore) {
            $filteredQualityScores = $this->filterQualityScoresByGrantedLocales($productModelWithQualityScore->qualityScores());

            return $productModelWithQualityScore->buildWithQualityScores($filteredQualityScores);
        }, $productModels->connectorProductModels());

        return new ConnectorProductModelList($connectorProductModelList->totalNumberOfProductModels(), $filteredProductModels);
    }

    private function filterQualityScoresByGrantedLocales(?QualityScoreCollection $productModelScores): QualityScoreCollection
    {
        $qualityScoresArray = null !== $productModelScores ? $productModelScores->qualityScores : [];
        $grantedLocaleCodes = $this->getUserGrantedLocales();

        $filteredQualityScores = array_map(function (array $value) use ($grantedLocaleCodes) {
            return array_filter(
                $value,
                fn ($localeCode) => in_array($localeCode, $grantedLocaleCodes),
                ARRAY_FILTER_USE_KEY
            );
        }, $qualityScoresArray);

        return new QualityScoreCollection($filteredQualityScores);
    }

    private function getUserGrantedLocales(): array
    {
        $user = $this->tokenStorage->getToken()->getUser();
        Assert::isInstanceOf($user, UserInterface::class);

        return $this->getAllViewableLocalesForUser->fetchAll($user->getId());
    }
}
