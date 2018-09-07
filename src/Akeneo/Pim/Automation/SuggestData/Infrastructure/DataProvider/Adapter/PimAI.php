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
use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionsResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Exception\ClientException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Authentication\AuthenticationApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\IdentifiersMapping\IdentifiersMappingApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Subscription\SubscriptionApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer\IdentifiersMappingNormalizer;

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

    /** @var IdentifiersMappingApiInterface */
    private $identifiersMappingApi;

    /** @var IdentifiersMappingNormalizer */
    private $identifiersMappingNormalizer;

    /**
     * @param AuthenticationApiInterface            $authenticationApi
     * @param SubscriptionApiInterface              $subscriptionApi
     * @param IdentifiersMappingRepositoryInterface $identifiersMappingRepository
     * @param IdentifiersMappingApiInterface        $identifiersMappingApi
     * @param IdentifiersMappingNormalizer          $identifiersMappingNormalizer
     */
    public function __construct(
        AuthenticationApiInterface $authenticationApi,
        SubscriptionApiInterface $subscriptionApi,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        IdentifiersMappingApiInterface $identifiersMappingApi,
        IdentifiersMappingNormalizer $identifiersMappingNormalizer
    ) {
        $this->authenticationApi = $authenticationApi;
        $this->subscriptionApi = $subscriptionApi;
        $this->identifiersMappingRepository = $identifiersMappingRepository;
        $this->identifiersMappingApi = $identifiersMappingApi;
        $this->identifiersMappingNormalizer = $identifiersMappingNormalizer;
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

        $productId = $request->getProduct()->getId();
        $family = $request->getProduct()->getFamily();
        $familyInfos = [
            'code' => $family->getCode(),
            'label' => [
                $family->getTranslation()->getLocale() => $family->getLabel()
            ]
        ];

        try {
            $clientResponse = $this->subscriptionApi->subscribeProduct($mapped, $productId, $familyInfos);
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
     *
     * @return bool
     */
    public function authenticate(string $token): bool
    {
        return $this->authenticationApi->authenticate($token);
    }

    /**
     * TODO: Deal with pagination (see APAI-192)
     *
     * @return ProductSubscriptionsResponse
     */
    public function fetch(): ProductSubscriptionsResponse
    {
        try {
            $clientResponse = $this->subscriptionApi->fetchProducts();
        } catch (ClientException $e) {
            throw new ProductSubscriptionException($e->getMessage());
        }

        $subscriptions = $clientResponse->content()->getSubscriptions();

        $subscriptionsResponse = new ProductSubscriptionsResponse();
        foreach ($subscriptions as $subscription) {
            $subscriptionResponse = new ProductSubscriptionResponse(
                42, // @TODO: Use tracker id (See APAI-153)
                $subscription->getSubscriptionId(),
                $subscription->getAttributes()
            );
            $subscriptionsResponse->add($subscriptionResponse);
        }
        return $subscriptionsResponse;
    }

    /**
     * {@inheritdoc}
     */
    public function updateIdentifiersMapping(IdentifiersMapping $identifiersMapping): void
    {
        $this->identifiersMappingApi->update($this->identifiersMappingNormalizer->normalize($identifiersMapping));
    }
}
