<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Adapter;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\ProductSubscriptionException;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Authentication\AuthenticationApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Subscription\SubscriptionApiInterface;
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
            throw new ProductSubscriptionException('No mapping defined');
        }

        $mapped = $request->getMappedValues($identifiersMapping);
        if (empty($mapped)) {
            throw new ProductSubscriptionException(
                sprintf('No mapped values for product %s', $request->getProduct()->getId())
            );
        }

        $clientResponse = $this->subscriptionApi->subscribeProduct($mapped);
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
