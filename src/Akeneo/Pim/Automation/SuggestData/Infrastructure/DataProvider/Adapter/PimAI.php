<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Adapter;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\ProductSubscriptionException;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionsResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Exception\ClientException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Authentication\AuthenticationApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Fetch\FetchApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Subscription\SubscriptionApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Exception\DataProviderException;

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
     * {@inheritdoc}
     */
    public function subscribe(ProductSubscriptionRequest $request): ProductSubscriptionResponse
    {
        $identifiersMapping = $this->identifiersMappingRepository->find();
        if ($identifiersMapping->isEmpty()) {
            throw new ProductSubscriptionException('No mapping defined');
        }

        $mapped = $request->getMappedValues($identifiersMapping);
        if (empty($mapped)) {
            throw new ProductSubscriptionException(
                sprintf('No mapped values for product with id "%s"', $request->getProduct()->getId())
            );
        }

        try {
            $clientResponse = $this->subscriptionApi->subscribeProduct($mapped);
        } catch (ClientException $e) {
            throw new ProductSubscriptionException($e->getMessage());
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
     * @return ProductSubscriptionsResponse
     */
    public function fetch(): ProductSubscriptionsResponse
    {
        $clientResponse = $this->subscriptionApi->fetchProducts();

        return new ProductSubscriptionsResponse(
            $clientResponse->content()->getSubscriptions()
        );
    }
}
