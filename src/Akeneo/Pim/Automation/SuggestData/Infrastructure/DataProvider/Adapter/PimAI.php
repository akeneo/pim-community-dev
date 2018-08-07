<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Adapter;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Authentication\AuthenticationApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Subscription\SubscriptionApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Exceptions\MappingNotDefinedException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\SuggestedDataCollectionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

/**
 * PIM.ai implementation to connect to a data provider
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class PimAI implements DataProviderInterface
{
    private $authenticationApi;

    private $subscriptionApi;

    private $identifiersMappingRepository;

    public function __construct(
        AuthenticationApiInterface $authenticationApi,
        SubscriptionApiInterface $subscriptionApi,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository
    ) {
        $this->authenticationApi = $authenticationApi;
        $this->subscriptionApi = $subscriptionApi;
        $this->identifiersMappingRepository = $identifiersMappingRepository;
    }

    public function subscribe(ProductSubscriptionRequest $request): ProductSubscriptionResponse
    {
        $identifiersMapping = $this->identifiersMappingRepository->find();
        if ($identifiersMapping->isEmpty()) {
            throw new MappingNotDefinedException();
        }

        //Todo : the client will throw exceptions if something wrong happened. No need to call the isSuccess() but use a try/catch instead
        $clientResponse = $this->subscriptionApi->subscribeProduct($request->getMappedValues($identifiersMapping));
        if (!$clientResponse->isSuccess()) {
            throw new \Exception('API error');
        }

        //TODO : see what to do in this case (should not happen but we still have to handle it)
        if (! $clientResponse->hasSubscriptions()) {
            throw new \Exception('No subscription found in the client response');
        }

        $subscriptions = $clientResponse->content();

        return new ProductSubscriptionResponse(
            $request->getProduct(),
            $subscriptions->getFirst()->getSubscriptionId(),
            $subscriptions->getFirst()->getAttributes()
        );
    }

    public function bulkPush(array $products): SuggestedDataCollectionInterface
    {
        throw new \LogicException('Not implemented');
    }

    public function pull(ProductInterface $product)
    {
        throw new \LogicException('Not implemented');
    }

    public function bulkPull(array $products)
    {
        throw new \LogicException('Not implemented');
    }

    public function authenticate(?string $token): bool
    {
        return $this->authenticationApi->authenticate($token);
    }

    public function configure(array $config)
    {
        throw new \LogicException('Not implemented');
    }
}
