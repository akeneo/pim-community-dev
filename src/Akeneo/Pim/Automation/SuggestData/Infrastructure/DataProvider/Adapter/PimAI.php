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
use Akeneo\Pim\Automation\SuggestData\Domain\Model\FamilyCode;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\FranklinAttributeId;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\AttributeOptionsMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Exception\ClientException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\AttributeOptionsMapping\AttributeOptionsMappingInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\AttributesMapping\AttributesMappingApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Authentication\AuthenticationApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\IdentifiersMapping\IdentifiersMappingApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Subscription\SubscriptionApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exception\BadRequestException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exception\InsufficientCreditsException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exception\InvalidTokenException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exception\PimAiServerException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\AttributeMapping;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\Subscription;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Converter\AttributeOptionsMapping\AttributeOptionsMappingConverter;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer\AttributesMappingNormalizer;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer\FamilyNormalizer;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer\IdentifiersMappingNormalizer;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\SubscriptionsCursor;

/**
 * PIM.ai implementation to connect to a data provider.
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

    /** @var AttributeOptionsMappingInterface */
    private $attributeOptionsMappingApi;

    /**
     * @param AuthenticationApiInterface $authenticationApi
     * @param SubscriptionApiInterface $subscriptionApi
     * @param IdentifiersMappingRepositoryInterface $identifiersMappingRepository
     * @param IdentifiersMappingApiInterface $identifiersMappingApi
     * @param AttributesMappingApiInterface $attributesMappingApi
     * @param AttributeOptionsMappingInterface $attributeOptionsMappingApi
     * @param IdentifiersMappingNormalizer $identifiersMappingNormalizer
     * @param AttributesMappingNormalizer $attributesMappingNormalizer
     * @param FamilyNormalizer $familyNormalizer
     */
    public function __construct(
        AuthenticationApiInterface $authenticationApi,
        SubscriptionApiInterface $subscriptionApi,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        IdentifiersMappingApiInterface $identifiersMappingApi,
        AttributesMappingApiInterface $attributesMappingApi,
        AttributeOptionsMappingInterface $attributeOptionsMappingApi,
        IdentifiersMappingNormalizer $identifiersMappingNormalizer,
        AttributesMappingNormalizer $attributesMappingNormalizer,
        FamilyNormalizer $familyNormalizer
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
    }

    /**
     * {@inheritdoc}
     */
    public function subscribe(ProductSubscriptionRequest $request): ProductSubscriptionResponse
    {
        $identifiersMapping = $this->identifiersMappingRepository->find();
        if ($identifiersMapping->isEmpty()) {
            throw ProductSubscriptionException::invalidIdentifiersMapping();
        }

        $product = $request->getProduct();
        $mapped = $request->getMappedValues($identifiersMapping);
        if (empty($mapped)) {
            throw ProductSubscriptionException::invalidMappedValues();
        }

        $familyInfos = $this->familyNormalizer->normalize($product->getFamily());
        try {
            $clientResponse = $this->subscriptionApi->subscribeProduct($mapped, $product->getId(), $familyInfos);
        } catch (InvalidTokenException $e) {
            throw ProductSubscriptionException::invalidToken();
        } catch (InsufficientCreditsException $e) {
            throw ProductSubscriptionException::insufficientCredits();
        } catch (BadRequestException | PimAiServerException $e) {
            throw new ProductSubscriptionException($e->getMessage(), $e->getCode());
        }
        $subscription = $clientResponse->content()->getFirst();

        return $this->buildSubscriptionResponse($subscription);
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
                $attribute->getPimAttributeCode(),
                $this->mapAttributeMappingStatus($attribute->getStatus()),
                $attribute->getType(),
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
    ): AttributeOptionsMapping {
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
}
