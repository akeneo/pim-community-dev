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
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Model\Read\Family;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Repository\FamilyRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception\ProductSubscriptionException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductSubscriptionResponseCollection;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Write\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SubscriptionId;
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

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class SubscriptionProvider extends AbstractProvider implements SubscriptionProviderInterface
{
    /** @var SubscriptionWebService */
    private $api;

    /** @var FamilyRepositoryInterface */
    private $familyRepository;

    public function __construct(
        SubscriptionWebService $api,
        ConfigurationRepositoryInterface $configurationRepository,
        FamilyRepositoryInterface $familyRepository
    ) {
        parent::__construct($configurationRepository);

        $this->api = $api;
        $this->familyRepository = $familyRepository;
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

        $clientRequest = new RequestCollection();
        $clientRequest->add($this->buildClientRequest($subscriptionRequest));

        $apiResponse = $this->doSubscribe($clientRequest);
        if ($apiResponse->hasWarnings()) {
            throw ProductSubscriptionException::invalidMappedValues();
        }

        $subscriptions = $apiResponse->subscriptions();

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

        $clientRequests = new RequestCollection();
        foreach ($subscriptionRequests as $subscriptionRequest) {
            $clientRequests->add($this->buildClientRequest($subscriptionRequest));
        }

        $response = $this->doSubscribe($clientRequests);

        $responses = new ProductSubscriptionResponseCollection($response->warnings()->toArray());
        foreach ($response->subscriptions() as $subscription) {
            $responses->add($this->buildSubscriptionResponse($subscription));
        }

        return $responses;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(\DateTime $updatedSince): \Iterator
    {
        $this->api->setToken($this->getToken());

        try {
            $subscriptionsPage = $this->api->fetchProducts(null, $updatedSince);
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
     * @param SubscriptionId $subscriptionId
     *
     * @throws DataProviderException
     */
    public function unsubscribe(SubscriptionId $subscriptionId): void
    {
        $this->api->setToken($this->getToken());

        try {
            $this->api->unsubscribeProduct((string) $subscriptionId);
        } catch (FranklinServerException $e) {
            throw DataProviderException::serverIsDown($e);
        } catch (InvalidTokenException $e) {
            throw DataProviderException::authenticationError($e);
        } catch (BadRequestException $e) {
            throw DataProviderException::badRequestError($e);
        }
    }

    /**
     * @param SubscriptionId $subscriptionId
     * @param Family $family
     *
     * @throws DataProviderException
     */
    public function updateFamilyInfos(SubscriptionId $subscriptionId, Family $family): void
    {
        $this->api->setToken($this->getToken());
        try {
            $normalizer = new FamilyNormalizer();
            $familyInfos = $normalizer->normalize($family);
            $this->api->updateFamilyInfos((string) $subscriptionId, $familyInfos);
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
     *
     * @throws ProductSubscriptionException
     *
     * @return Request
     */
    private function buildClientRequest(ProductSubscriptionRequest $subscriptionRequest): Request
    {
        $identifiers = $subscriptionRequest->getMappedValues();
        if (empty($identifiers)) {
            throw ProductSubscriptionException::invalidMappedValues();
        }

        $familyNormalizer = new FamilyNormalizer();
        $familyInfos = $familyNormalizer->normalize($subscriptionRequest->getFamily());
        $trackerId = $subscriptionRequest->getProductId()->toInt();

        return new Request($identifiers, $trackerId, $familyInfos);
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
            new ProductId($subscription->getTrackerId()),
            new SubscriptionId($subscription->getSubscriptionId()),
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
    }
}
