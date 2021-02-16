<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Component\Connector;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleRateCollection;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\GetProductsWithQualityScoresInterface;
use Akeneo\Pim\Permission\Component\Query\GetAllViewableLocalesForUser;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Webmozart\Assert\Assert;

final class GetProductsWithQualityScoresWithPermissions implements GetProductsWithQualityScoresInterface
{
    private GetProductsWithQualityScoresInterface $getProductsWithQualityScores;

    private GetAllViewableLocalesForUser $getAllViewableLocalesForUser;

    private TokenStorageInterface $tokenStorage;

    public function __construct(
        GetProductsWithQualityScoresInterface $getProductsWithQualityScores,
        GetAllViewableLocalesForUser $getAllViewableLocalesForUser,
        TokenStorageInterface $tokenStorage
    ) {
        $this->getProductsWithQualityScores = $getProductsWithQualityScores;
        $this->getAllViewableLocalesForUser = $getAllViewableLocalesForUser;
        $this->tokenStorage = $tokenStorage;
    }

    public function fromConnectorProduct(ConnectorProduct $product): ConnectorProduct
    {
        $productWithQualityScore = $this->getProductsWithQualityScores->fromConnectorProduct($product);

        $filteredQualityScores = $this->filterQualityScoresByGrantedLocales($productWithQualityScore->qualityScores());

        return $product->buildWithQualityScores($filteredQualityScores);
    }

    public function fromConnectorProductList(ConnectorProductList $connectorProductList, ?string $channel = null, array $locales = []): ConnectorProductList
    {
        $products = $this->getProductsWithQualityScores->fromConnectorProductList($connectorProductList, $channel, $locales);

        $filteredProducts = array_map(function (ConnectorProduct $productWithQualityScore) {
            $filteredQualityScores = $this->filterQualityScoresByGrantedLocales($productWithQualityScore->qualityScores());

            return $productWithQualityScore->buildWithQualityScores($filteredQualityScores);
        }, $products->connectorProducts());

        return new ConnectorProductList($connectorProductList->totalNumberOfProducts(), $filteredProducts);
    }

    public function fromNormalizedProduct(string $productIdentifier, array $normalizedProduct, ?string $channel = null, array $locales = []): array
    {
        $grantedLocales = array_values(array_intersect($locales, $this->getUserGrandtedLocales()));

        return empty($grantedLocales)
            ? $normalizedProduct
            : $this->getProductsWithQualityScores->fromNormalizedProduct($productIdentifier, $normalizedProduct, $channel, $grantedLocales);
    }

    private function filterQualityScoresByGrantedLocales(?ChannelLocaleRateCollection $qualityScores)
    {
        if ($qualityScores === null) {
            return new ChannelLocaleRateCollection();
        }

        $grantedLocaleCodes = $this->getUserGrandtedLocales();

        $filteredQualityScores = array_map(function (array $value) use ($grantedLocaleCodes) {
            return array_filter(
                $value,
                fn ($localeCode) => in_array($localeCode, $grantedLocaleCodes),
                ARRAY_FILTER_USE_KEY
            );
        }, $qualityScores->toArrayInt());

        return ChannelLocaleRateCollection::fromArrayInt($filteredQualityScores);
    }

    private function getUserGrandtedLocales(): array
    {
        $user = $this->tokenStorage->getToken()->getUser();
        Assert::isInstanceOf($user, UserInterface::class);

        return $this->getAllViewableLocalesForUser->fetchAll($user->getId());
    }
}
