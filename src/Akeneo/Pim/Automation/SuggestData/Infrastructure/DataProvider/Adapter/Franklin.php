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

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\AttributeOptionsMappingProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\AttributesMappingProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\AuthenticationProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\IdentifiersMappingProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\SubscriptionProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Read\AttributesMappingResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\FamilyCode;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\FranklinAttributeId;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionResponseCollection;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\AttributeOptionsMapping as ReadAttributeOptionsMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Write\AttributeOptionsMapping as WriteAttributeOptionsMapping;

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

    /** @var SubscriptionProviderInterface */
    private $subscriptionProvider;

    /** @var IdentifiersMappingProviderInterface */
    private $identifiersMappingProvider;

    /** @var AttributeOptionsMappingProviderInterface */
    private $attributeOptionsMappingProvider;

    /**
     * @param AuthenticationProviderInterface $authenticationProvider
     * @param SubscriptionProviderInterface $subscriptionProvider
     * @param AttributesMappingProviderInterface $attributesMappingProvider
     * @param IdentifiersMappingProviderInterface $identifiersMappingProvider
     * @param AttributeOptionsMappingProviderInterface $attributeOptionsMappingProvider
     */
    public function __construct(
        AuthenticationProviderInterface $authenticationProvider,
        SubscriptionProviderInterface $subscriptionProvider,
        AttributesMappingProviderInterface $attributesMappingProvider,
        IdentifiersMappingProviderInterface $identifiersMappingProvider,
        AttributeOptionsMappingProviderInterface $attributeOptionsMappingProvider
    ) {
        $this->authenticationProvider = $authenticationProvider;
        $this->subscriptionProvider = $subscriptionProvider;
        $this->attributesMappingProvider = $attributesMappingProvider;
        $this->identifiersMappingProvider = $identifiersMappingProvider;
        $this->attributeOptionsMappingProvider = $attributeOptionsMappingProvider;
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
        $this->identifiersMappingProvider->updateIdentifiersMapping($identifiersMapping);
    }

    /**
     * {@inheritdoc}
     */
    public function unsubscribe(string $subscriptionId): void
    {
        $this->subscriptionProvider->unsubscribe($subscriptionId);
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
        return $this->attributeOptionsMappingProvider->getAttributeOptionsMapping($familyCode, $franklinAttributeId);
    }

    /**
     * {@inheritdoc}
     */
    public function saveAttributeOptionsMapping(
        FamilyCode $familyCode,
        FranklinAttributeId $franklinAttributeId,
        WriteAttributeOptionsMapping $attributeOptionsMapping
    ): void {
        $this->attributeOptionsMappingProvider->saveAttributeOptionsMapping(
            $familyCode,
            $franklinAttributeId,
            $attributeOptionsMapping
        );
    }
}
