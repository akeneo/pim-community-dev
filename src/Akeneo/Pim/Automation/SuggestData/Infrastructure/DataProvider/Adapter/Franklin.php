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

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\AttributesMappingProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\AuthenticationProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\SubscriptionProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\ProductSubscriptionException;
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
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\IdentifiersMapping\IdentifiersMappingApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\OptionsMapping\OptionsMappingInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\Subscription\SubscriptionApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Exception\ClientException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Converter\AttributeOptionsMappingConverter;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer\AttributeOptionsMappingNormalizer;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\DataProvider\Normalizer\IdentifiersMappingNormalizer;

/**
 * Franklin implementation to connect to a data provider.
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class Franklin implements DataProviderInterface
{
    /** @var AuthenticationProviderInterface */
    private $authenticationProvider;

    /** @var AttributesMappingProviderInterface */
    private $attributesMappingProvider;

    /** @var SubscriptionApiInterface */
    private $subscriptionApi;

    /** @var IdentifiersMappingApiInterface */
    private $identifiersMappingApi;

    /** @var IdentifiersMappingNormalizer */
    private $identifiersMappingNormalizer;

    /** @var OptionsMappingInterface */
    private $attributeOptionsMappingApi;

    /** @var ConfigurationRepositoryInterface */
    private $configurationRepository;

    /** @var Token */
    private $token;

    /** @var SubscriptionProviderInterface */
    private $subscriptionProvider;

    /**
     * @param AuthenticationProviderInterface $authenticationProvider
     * @param SubscriptionApiInterface $subscriptionApi
     * @param IdentifiersMappingApiInterface $identifiersMappingApi
     * @param OptionsMappingInterface $attributeOptionsMappingApi
     * @param IdentifiersMappingNormalizer $identifiersMappingNormalizer
     * @param ConfigurationRepositoryInterface $configurationRepository
     * @param SubscriptionProviderInterface $subscriptionProvider
     * @param AttributesMappingProviderInterface $attributesMappingProvider
     */
    public function __construct(
        AuthenticationProviderInterface $authenticationProvider,
        SubscriptionApiInterface $subscriptionApi,
        IdentifiersMappingApiInterface $identifiersMappingApi,
        OptionsMappingInterface $attributeOptionsMappingApi,
        IdentifiersMappingNormalizer $identifiersMappingNormalizer,
        ConfigurationRepositoryInterface $configurationRepository,
        SubscriptionProviderInterface $subscriptionProvider,
        AttributesMappingProviderInterface $attributesMappingProvider
    ) {
        $this->authenticationProvider = $authenticationProvider;
        $this->subscriptionApi = $subscriptionApi;
        $this->identifiersMappingApi = $identifiersMappingApi;
        $this->attributeOptionsMappingApi = $attributeOptionsMappingApi;
        $this->identifiersMappingNormalizer = $identifiersMappingNormalizer;
        $this->configurationRepository = $configurationRepository;
        $this->subscriptionProvider = $subscriptionProvider;
        $this->attributesMappingProvider = $attributesMappingProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function subscribe(ProductSubscriptionRequest $subscriptionRequest): ProductSubscriptionResponse
    {
        return $this->subscriptionProvider->subscribe($subscriptionRequest);
    }

    /**
     * {@inheritdoc}
     */
    public function bulkSubscribe(array $subscriptionRequests): ProductSubscriptionResponseCollection
    {
        return $this->subscriptionProvider->bulkSubscribe($subscriptionRequests);
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(Token $token): bool
    {
        return $this->authenticationProvider->authenticate($token);
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(): \Iterator
    {
        return $this->subscriptionProvider->fetch();
    }

    /**
     * {@inheritdoc}
     */
    public function updateIdentifiersMapping(IdentifiersMapping $identifiersMapping): void
    {
        $this->identifiersMappingApi->setToken($this->getToken());
        $this->identifiersMappingApi->update($this->identifiersMappingNormalizer->normalize($identifiersMapping));
    }

    /**
     * @param string $subscriptionId
     *
     * @throws ProductSubscriptionException
     */
    public function unsubscribe(string $subscriptionId): void
    {
        $this->subscriptionApi->setToken($this->getToken());
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
        return $this->attributesMappingProvider->getAttributesMapping($familyCode);
    }

    /**
     * {@inheritdoc}
     */
    public function updateAttributesMapping(string $familyCode, array $attributesMapping): void
    {
        $this->attributesMappingProvider->updateAttributesMapping($familyCode, $attributesMapping);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeOptionsMapping(
        FamilyCode $familyCode,
        FranklinAttributeId $franklinAttributeId
    ): ReadAttributeOptionsMapping {
        $this->attributeOptionsMappingApi->setToken($this->getToken());
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
        $this->attributeOptionsMappingApi->setToken($this->getToken());
        $attributeOptionsMappingNormalize = new AttributeOptionsMappingNormalizer();

        $this->attributeOptionsMappingApi->update(
            (string) $familyCode,
            (string) $franklinAttributeId,
            $attributeOptionsMappingNormalize->normalize($attributeOptionsMapping)
        );
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
