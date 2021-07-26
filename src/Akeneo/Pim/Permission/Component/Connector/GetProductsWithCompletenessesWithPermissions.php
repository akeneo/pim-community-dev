<?php

declare(strict_types=1);

namespace Akeneo\Pim\Permission\Component\Connector;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProduct;
use Akeneo\Pim\Enrichment\Component\Product\Connector\ReadModel\ConnectorProductList;
use Akeneo\Pim\Enrichment\Component\Product\Connector\UseCase\GetProductsWithCompletenessesInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Permission\GetAllViewableLocalesForUser;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Webmozart\Assert\Assert;

class GetProductsWithCompletenessesWithPermissions implements GetProductsWithCompletenessesInterface
{
    private GetProductsWithCompletenessesInterface $getProductsWithCompletenesses;
    private GetAllViewableLocalesForUser $getAllViewableLocalesForUser;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        GetProductsWithCompletenessesInterface $getProductsWithCompletenesses,
        GetAllViewableLocalesForUser $getAllViewableLocalesForUser,
        TokenStorageInterface $tokenStorage
    ) {
        $this->getProductsWithCompletenesses = $getProductsWithCompletenesses;
        $this->getAllViewableLocalesForUser = $getAllViewableLocalesForUser;
        $this->tokenStorage = $tokenStorage;
    }

    public function fromConnectorProduct(ConnectorProduct $product): ConnectorProduct
    {
        $viewableLocales = $this->getAllViewableLocalesForUser->fetchAll($this->getUserId());
        $productWithCompletenesses = $this->getProductsWithCompletenesses->fromConnectorProduct($product);

        return $productWithCompletenesses->buildWithCompletenesses(
            new ProductCompletenessCollection(
                $productWithCompletenesses->id(),
                array_filter(
                    (array) $productWithCompletenesses->completenesses()->getIterator(),
                    fn (ProductCompleteness $completeness): bool => in_array($completeness->localeCode(), $viewableLocales)
                )
            )
        );
    }

    public function fromConnectorProductList(ConnectorProductList $connectorProductList, ?string $channel = null, array $locales = []): ConnectorProductList
    {
        $allViewableLocales = $this->getAllViewableLocalesForUser->fetchAll($this->getUserId());
        $viewableLocales = empty($locales) ? $allViewableLocales : array_intersect($locales, $allViewableLocales);

        return $this->getProductsWithCompletenesses->fromConnectorProductList(
            $connectorProductList,
            $channel,
            $viewableLocales
        );
    }

    private function getUserId(): int
    {
        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            throw new \DomainException('A user must be connected to apply permissions.');
        }
        $user = $token->getUser();
        Assert::isInstanceOf($user, UserInterface::class);

        return $user->getId();
    }
}
