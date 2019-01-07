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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Adapter;

use Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider\SubscriptionProviderInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Exception\DataProviderException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception\ProductSubscriptionException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductSubscriptionResponseCollection;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Write\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\ApiResponse;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\Subscription\Request;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\Subscription\RequestCollection;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\Subscription\SubscriptionWebService;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\BadRequestException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\FranklinServerException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\InsufficientCreditsException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\InvalidTokenException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\Subscription;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\Normalizer\FamilyNormalizer;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\DataProvider\SubscriptionsCursor;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class SubscriptionProvider extends AbstractProvider implements SubscriptionProviderInterface
{
    /** @var IdentifiersMappingRepositoryInterface */
    private $identifiersMappingRepository;

    /** @var SubscriptionWebService */
    private $api;

    /**
     * @param IdentifiersMappingRepositoryInterface $identifiersMappingRepository
     * @param SubscriptionWebService $api
     * @param ConfigurationRepositoryInterface $configurationRepository
     */
    public function __construct(
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        SubscriptionWebService $api,
        ConfigurationRepositoryInterface $configurationRepository
    ) {
        parent::__construct($configurationRepository);

        $this->identifiersMappingRepository = $identifiersMappingRepository;
        $this->api = $api;
    }

    /**
     * @param ProductSubscriptionRequest $subscriptionRequest
     *
     * @throws ProductSubscriptionException
     * @throws DataProviderException
     *
     * @return ProductSubscriptionResponse
     */
    public function subscribe(ProductSubscriptionRequest $subscriptionRequest): ProductSubscriptionResponse
    {
        $this->api->setToken($this->getToken());
        $identifiersMapping = $this->identifiersMappingRepository->find();
        if ($identifiersMapping->isEmpty()) {
            throw ProductSubscriptionException::invalidIdentifiersMapping();
        }

        $clientRequest = new RequestCollection();
        $clientRequest->add($this->buildClientRequest($subscriptionRequest, $identifiersMapping));
        $subscriptions = $this->doSubscribe($clientRequest)->subscriptions();

        if (null === $subscriptions->first()) {
            throw DataProviderException::badRequestError();
        }

        return $this->buildSubscriptionResponse($subscriptions->first());
    }

    /**
     * @param ProductSubscriptionRequest[] $subscriptionRequests
     *
     * @throws ProductSubscriptionException
     * @throws DataProviderException
     *
     * @return ProductSubscriptionResponseCollection
     */
    public function bulkSubscribe(array $subscriptionRequests): ProductSubscriptionResponseCollection
    {
        $this->api->setToken($this->getToken());
        $identifiersMapping = $this->identifiersMappingRepository->find();
        if ($identifiersMapping->isEmpty()) {
            throw ProductSubscriptionException::invalidIdentifiersMapping();
        }

        $clientRequests = new RequestCollection();
        foreach ($subscriptionRequests as $subscriptionRequest) {
            $clientRequests->add($this->buildClientRequest($subscriptionRequest, $identifiersMapping));
        }

        $response = $this->doSubscribe($clientRequests);

        $responses = new ProductSubscriptionResponseCollection($response->warnings()->toArray());
        foreach ($response->subscriptions() as $subscription) {
            $responses->add($this->buildSubscriptionResponse($subscription));
        }

        return $responses;
    }

    /**
     * @throws DataProviderException
     *
     * @return SubscriptionsCursor
     */
    public function fetch(): \Iterator
    {
        $this->api->setToken($this->getToken());

        try {
            $subscriptionsPage = $this->api->fetchProducts();
        } catch (FranklinServerException $e) {
            throw DataProviderException::serverIsDown($e);
        } catch (InvalidTokenException $e) {
            throw DataProviderException::authenticationError($e);
        } catch (BadRequestException $e) {
            throw DataProviderException::badRequestError($e);
        }

        return new SubscriptionsCursor($subscriptionsPage);
    }

    /**
     * @param string $subscriptionId
     *
     * @throws DataProviderException
     */
    public function unsubscribe(string $subscriptionId): void
    {
        $this->api->setToken($this->getToken());

        try {
            $this->api->unsubscribeProduct($subscriptionId);
        } catch (FranklinServerException $e) {
            throw DataProviderException::serverIsDown($e);
        } catch (InvalidTokenException $e) {
            throw DataProviderException::authenticationError($e);
        } catch (BadRequestException $e) {
            throw DataProviderException::badRequestError($e);
        }
    }

    /**
     * @param string $subscriptionId
     * @param FamilyInterface $family
     *
     * @throws DataProviderException
     */
    public function updateFamilyInfos(string $subscriptionId, FamilyInterface $family): void
    {
        $this->api->setToken($this->getToken());
        try {
            $normalizer = new FamilyNormalizer();
            $familyInfos = $normalizer->normalize($family);
            $this->api->updateFamilyInfos($subscriptionId, $familyInfos);
        } catch (FranklinServerException $e) {
            throw DataProviderException::serverIsDown($e);
        } catch (InvalidTokenException $e) {
            throw DataProviderException::authenticationError($e);
        } catch (BadRequestException $e) {
            throw DataProviderException::badRequestError($e);
        }
    }

    /**
     * @param ProductSubscriptionRequest $subscriptionRequest
     * @param IdentifiersMapping $identifiersMapping
     *
     * @throws ProductSubscriptionException
     *
     * @return Request
     */
    private function buildClientRequest(
        ProductSubscriptionRequest $subscriptionRequest,
        IdentifiersMapping $identifiersMapping
    ): Request {
        $product = $subscriptionRequest->getProduct();
        $mapped = $subscriptionRequest->getMappedValues($identifiersMapping);
        if (empty($mapped)) {
            throw ProductSubscriptionException::invalidMappedValues();
        }
        $normalizer = new FamilyNormalizer();
        $familyInfos = $normalizer->normalize($product->getFamily());

        return new Request($mapped, $product->getId(), $familyInfos);
    }

    /**
     * @param Subscription $subscription
     *
     * @return ProductSubscriptionResponse
     */
    private function buildSubscriptionResponse(Subscription $subscription): ProductSubscriptionResponse
    {
        $suggestedValues = array_map(
            function (array $data) {
                return [
                    'pimAttributeCode' => $data['name'],
                    'value' => $data['value'],
                ];
            },
            $subscription->getAttributes()
        );

        return new ProductSubscriptionResponse(
            $subscription->getTrackerId(),
            $subscription->getSubscriptionId(),
            $suggestedValues,
            $subscription->isMappingMissing(),
            $subscription->isCancelled()
        );
    }

    /**
     * @param RequestCollection $clientRequests
     *
     * @throws ProductSubscriptionException
     * @throws DataProviderException
     *
     * @return ApiResponse
     */
    private function doSubscribe(RequestCollection $clientRequests): ApiResponse
    {
        try {
            return $this->api->subscribe($clientRequests);
        } catch (FranklinServerException $e) {
            throw DataProviderException::serverIsDown($e);
        } catch (InvalidTokenException $e) {
            throw DataProviderException::authenticationError($e);
        } catch (InsufficientCreditsException $e) {
            throw ProductSubscriptionException::insufficientCredits();
        } catch (BadRequestException $e) {
            throw DataProviderException::badRequestError($e);
        }

        return $clientResponse->content();
    }
}
