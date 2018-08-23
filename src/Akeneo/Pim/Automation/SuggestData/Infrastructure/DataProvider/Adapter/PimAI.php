<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Adapter;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionsResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Authentication\AuthenticationApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Fetch\FetchApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Subscription\SubscriptionApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exceptions\PimAiServerException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Exceptions\MappingNotDefinedException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\SuggestedDataCollectionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use GuzzleHttp\Exception\ServerException;

/**
 * PIM.ai implementation to connect to a data provider
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class PimAI implements DataProviderInterface
{
    /** @var AuthenticationApiInterface */
    private $authenticationApi;

    /** @var SubscriptionApiInterface */
    private $subscriptionApi;

    /** @var IdentifiersMappingRepositoryInterface */
    private $identifiersMappingRepository;

    /**
     * @param AuthenticationApiInterface $authenticationApi
     * @param SubscriptionApiInterface $subscriptionApi
     * @param IdentifiersMappingRepositoryInterface $identifiersMappingRepository
     */
    public function __construct(
        AuthenticationApiInterface $authenticationApi,
        SubscriptionApiInterface $subscriptionApi,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository
    ) {
        $this->authenticationApi = $authenticationApi;
        $this->subscriptionApi = $subscriptionApi;
        $this->identifiersMappingRepository = $identifiersMappingRepository;
    }

    /**
     * @param ProductSubscriptionRequest $request
     * @return ProductSubscriptionResponse
     * @throws MappingNotDefinedException
     */
    public function subscribe(ProductSubscriptionRequest $request): ProductSubscriptionResponse
    {
        $identifiersMapping = $this->identifiersMappingRepository->find();
        if ($identifiersMapping->isEmpty()) {
            throw new MappingNotDefinedException();
        }

        $clientResponse = $this->subscriptionApi->subscribeProduct($request->getMappedValues($identifiersMapping));

        //TODO : see what to do in this case
        if (! $clientResponse->hasSubscriptions()) {
            throw new \Exception('No subscription found in the client response');
        }

        $subscriptions = $clientResponse->content();

        return new ProductSubscriptionResponse(
            $request->getProduct()->getId(),
            $subscriptions->getFirst()->getSubscriptionId(),
            $subscriptions->getFirst()->getAttributes()
        );
    }

    /**
     * @param string $token
     * @return bool
     */
    public function authenticate(string $token): bool
    {
        return $this->authenticationApi->authenticate($token);
    }

    /**
     * TODO: Deal with pagination
     *
     *
     * @return ProductSubscriptionsResponse
     * @throws PimAiServerException
     */
    public function fetch(): ProductSubscriptionsResponse
    {
        $clientResponse = $this->subscriptionApi->fetchProducts();

        return new ProductSubscriptionsResponse(
            $clientResponse->content()->getSubscriptions()
        );
    }
}
