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
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\ProductSubscriptionException;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\AttributeMapping as DomainAttributeMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\AttributesMappingResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Configuration;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\FamilyCode;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\FranklinAttributeId;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionResponseCollection;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\AttributeOptionsMapping as ReadAttributeOptionsMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Write\AttributeOptionsMapping as WriteAttributeOptionsMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\AttributesMapping\AttributesMappingApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Authentication\AuthenticationApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\IdentifiersMapping\IdentifiersMappingApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\OptionsMapping\OptionsMappingInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Subscription\SubscriptionApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Subscription\Write\Request;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Subscription\Write\RequestCollection;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exception\BadRequestException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exception\ClientException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exception\FranklinServerException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exception\InsufficientCreditsException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exception\InvalidTokenException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\AttributeMapping;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\Subscription;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\SubscriptionCollection;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Converter\AttributeOptionsMappingConverter;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer\AttributeOptionsMappingNormalizer;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer\AttributesMappingNormalizer;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer\FamilyNormalizer;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer\IdentifiersMappingNormalizer;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\SubscriptionsCursor;

/**
 * Franklin implementation to connect to a data provider.
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

    /** @var AttributesMappingApiInterface */
    private $attributesMappingApi;

    /** @var IdentifiersMappingNormalizer */
    private $identifiersMappingNormalizer;

    /** @var AttributesMappingNormalizer */
    private $attributesMappingNormalizer;

    /** @var FamilyNormalizer */
    private $familyNormalizer;

    /** @var OptionsMappingInterface */
    private $attributeOptionsMappingApi;

    /** @var ConfigurationRepositoryInterface */
    private $configurationRepository;

    /** @var Token */
    private $token;

    /**
     * @param AuthenticationApiInterface $authenticationApi
     * @param SubscriptionApiInterface $subscriptionApi
     * @param IdentifiersMappingRepositoryInterface $identifiersMappingRepository
     * @param IdentifiersMappingApiInterface $identifiersMappingApi
     * @param AttributesMappingApiInterface $attributesMappingApi
     * @param OptionsMappingInterface $attributeOptionsMappingApi
     * @param IdentifiersMappingNormalizer $identifiersMappingNormalizer
     * @param AttributesMappingNormalizer $attributesMappingNormalizer
     * @param FamilyNormalizer $familyNormalizer
     * @param ConfigurationRepositoryInterface $configurationRepository
     */
    public function __construct(
        AuthenticationApiInterface $authenticationApi,
        SubscriptionApiInterface $subscriptionApi,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        IdentifiersMappingApiInterface $identifiersMappingApi,
        AttributesMappingApiInterface $attributesMappingApi,
        OptionsMappingInterface $attributeOptionsMappingApi,
        IdentifiersMappingNormalizer $identifiersMappingNormalizer,
        AttributesMappingNormalizer $attributesMappingNormalizer,
        FamilyNormalizer $familyNormalizer,
        ConfigurationRepositoryInterface $configurationRepository
    ) {
        $this->authenticationApi = $authenticationApi;
        $this->subscriptionApi = $subscriptionApi;
        $this->identifiersMappingRepository = $identifiersMappingRepository;
        $this->identifiersMappingApi = $identifiersMappingApi;
        $this->attributesMappingApi = $attributesMappingApi;
        $this->attributeOptionsMappingApi = $attributeOptionsMappingApi;
        $this->identifiersMappingNormalizer = $identifiersMappingNormalizer;
        $this->attributesMappingNormalizer = $attributesMappingNormalizer;
        $this->familyNormalizer = $familyNormalizer;
        $this->configurationRepository = $configurationRepository;

        $this->identifiersMappingApi->setToken($this->getToken());
        $this->attributesMappingApi->setToken($this->getToken());
        $this->attributeOptionsMappingApi->setToken($this->getToken());
        $this->subscriptionApi->setToken($this->getToken());
    }

    /**
     * {@inheritdoc}
     */
    public function subscribe(ProductSubscriptionRequest $subscriptionRequest): ProductSubscriptionResponse
    {
        $identifiersMapping = $this->identifiersMappingRepository->find();
        if ($identifiersMapping->isEmpty()) {
            throw ProductSubscriptionException::invalidIdentifiersMapping();
        }

        $clientRequest = new RequestCollection();
        $clientRequest->add($this->buildClientRequest($subscriptionRequest, $identifiersMapping));
        $subscriptions = $this->doSubscribe($clientRequest);

        return $this->buildSubscriptionResponse($subscriptions->getFirst());
    }

    /**
     * @param ProductSubscriptionRequest[] $subscriptionRequests
     *
     * @return ProductSubscriptionResponseCollection
     */
    public function bulkSubscribe(array $subscriptionRequests): ProductSubscriptionResponseCollection
    {
        $identifiersMapping = $this->identifiersMappingRepository->find();
        if ($identifiersMapping->isEmpty()) {
            throw ProductSubscriptionException::invalidIdentifiersMapping();
        }
        $warnings = [];

        $clientRequest = new RequestCollection();
        foreach ($subscriptionRequests as $subscriptionRequest) {
            try {
                $clientRequest->add($this->buildClientRequest($subscriptionRequest, $identifiersMapping));
            } catch (ProductSubscriptionException $e) {
                $warnings[] = $e;
            }
        }

        $response = $this->doSubscribe($clientRequest);

        $responses = new ProductSubscriptionResponseCollection($warnings);
        foreach ($response->getSubscriptions() as $subscription) {
            $responses->add($this->buildSubscriptionResponse($subscription));
        }

        return $responses;
    }

    /**
     * @param Token $token
     *
     * @return bool
     */
    public function authenticate(Token $token): bool
    {
        return $this->authenticationApi->authenticate((string) $token);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(): \Iterator
    {
        try {
            $subscriptionsPage = $this->subscriptionApi->fetchProducts();
        } catch (ClientException $e) {
            throw new ProductSubscriptionException($e->getMessage());
        }

        return new SubscriptionsCursor($subscriptionsPage);
    }

    /**
     * {@inheritdoc}
     */
    public function updateIdentifiersMapping(IdentifiersMapping $identifiersMapping): void
    {
        $this->identifiersMappingApi->update($this->identifiersMappingNormalizer->normalize($identifiersMapping));
    }

    /**
     * @param string $subscriptionId
     *
     * @throws ProductSubscriptionException
     */
    public function unsubscribe(string $subscriptionId): void
    {
        try {
            $this->subscriptionApi->unsubscribeProduct($subscriptionId);
        } catch (ClientException $e) {
            throw new ProductSubscriptionException($e->getMessage());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributesMapping(string $familyCode): AttributesMappingResponse
    {
        $apiResponse = $this->attributesMappingApi->fetchByFamily($familyCode);

        $attributesMapping = new AttributesMappingResponse();
        foreach ($apiResponse as $attribute) {
            $attribute = new DomainAttributeMapping(
                $attribute->getTargetAttributeCode(),
                $attribute->getTargetAttributeLabel(),
                $attribute->getTargetAttributeType(),
                $attribute->getPimAttributeCode(),
                $this->mapAttributeMappingStatus($attribute->getStatus()),
                $attribute->getSummary()
            );
            $attributesMapping->addAttribute($attribute);
        }

        return $attributesMapping;
    }

    /**
     * {@inheritdoc}
     */
    public function updateAttributesMapping(string $familyCode, array $attributesMapping): void
    {
        $mapping = $this->attributesMappingNormalizer->normalize($attributesMapping);

        $this->attributesMappingApi->update($familyCode, $mapping);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeOptionsMapping(
        FamilyCode $familyCode,
        FranklinAttributeId $franklinAttributeId
    ): ReadAttributeOptionsMapping {
        $franklinOptionsMapping = $this
            ->attributeOptionsMappingApi
            ->fetchByFamilyAndAttribute((string) $familyCode, (string) $franklinAttributeId);

        $converter = new AttributeOptionsMappingConverter();

        return $converter->clientToApplication(
            (string) $familyCode,
            (string) $franklinAttributeId,
            $franklinOptionsMapping
        );
    }

    /**
     * {@inheritdoc}
     */
    public function saveAttributeOptionsMapping(
        FamilyCode $familyCode,
        FranklinAttributeId $franklinAttributeId,
        WriteAttributeOptionsMapping $attributeOptionsMapping
    ): void {
        $attributeOptionsMappingNormalize = new AttributeOptionsMappingNormalizer();

        $this->attributeOptionsMappingApi->update(
            (string) $familyCode,
            (string) $franklinAttributeId,
            $attributeOptionsMappingNormalize->normalize($attributeOptionsMapping)
        );
    }

    /**
     * @param ProductSubscriptionRequest $subscriptionRequest
     * @param IdentifiersMapping $identifiersMapping
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

        $familyInfos = $this->familyNormalizer->normalize($product->getFamily());

        return new Request($mapped, $product->getId(), $familyInfos);
    }

    /**
     * @param Subscription $subscription
     *
     * @return ProductSubscriptionResponse
     */
    private function buildSubscriptionResponse(Subscription $subscription): ProductSubscriptionResponse
    {
        return new ProductSubscriptionResponse(
            $subscription->getTrackerId(),
            $subscription->getSubscriptionId(),
            $subscription->getAttributes(),
            $subscription->isMappingMissing()
        );
    }

    /**
     * @param RequestCollection $clientRequest
     *
     * @throws ProductSubscriptionException
     *
     * @return SubscriptionCollection
     */
    private function doSubscribe(RequestCollection $clientRequest): SubscriptionCollection
    {
        try {
            $clientResponse = $this->subscriptionApi->subscribe($clientRequest);
        } catch (InvalidTokenException $e) {
            throw ProductSubscriptionException::invalidToken();
        } catch (InsufficientCreditsException $e) {
            throw ProductSubscriptionException::insufficientCredits();
        } catch (BadRequestException | FranklinServerException $e) {
            throw new ProductSubscriptionException($e->getMessage(), $e->getCode());
        }

        return $clientResponse->content();
    }

    /**
     * @param string $status
     *
     * @return int
     */
    private function mapAttributeMappingStatus(string $status): int
    {
        $mapping = [
            AttributeMapping::STATUS_PENDING => DomainAttributeMapping::ATTRIBUTE_PENDING,
            AttributeMapping::STATUS_INACTIVE => DomainAttributeMapping::ATTRIBUTE_UNMAPPED,
            AttributeMapping::STATUS_ACTIVE => DomainAttributeMapping::ATTRIBUTE_MAPPED,
        ];

        if (!array_key_exists($status, $mapping)) {
            throw new \InvalidArgumentException(sprintf('Unknown mapping attribute status "%s"', $status));
        }

        return $mapping[$status];
    }

    /**
     * @return string
     */
    private function getToken(): string
    {
        if (null === $this->token) {
            $config = $this->configurationRepository->find();
            if ($config instanceof Configuration) {
                $this->token = $config->getToken();
            }
        }

        return (string) $this->token;
    }
}
