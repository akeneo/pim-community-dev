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

namespace Akeneo\Pim\Automation\SuggestData\Application\DataProvider;

use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Read\AttributesMappingResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\Configuration\ValueObject\Token;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\ProductSubscriptionException;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\FamilyCode;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\FranklinAttributeId;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\AttributeOptionsMapping as ReadAttributeOptionsMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\Write\AttributeOptionsMapping as WriteAttributeOptionsMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Model\Read\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Model\Read\ProductSubscriptionResponseCollection;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Model\Write\ProductSubscriptionRequest;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
interface DataProviderInterface
{
    /**
     * @param ProductSubscriptionRequest $request
     *
     * @throws ProductSubscriptionException
     *
     * @return ProductSubscriptionResponse
     */
    public function subscribe(ProductSubscriptionRequest $request): ProductSubscriptionResponse;

    /**
     * @param ProductSubscriptionRequest[] $requests
     *
     * @throws ProductSubscriptionException
     *
     * @return ProductSubscriptionResponseCollection
     */
    public function bulkSubscribe(array $requests): ProductSubscriptionResponseCollection;

    /**
     * @param Token $token
     *
     * @return bool
     */
    public function authenticate(Token $token): bool;

    /**
     * @throws ProductSubscriptionException
     *
     * @return \Iterator
     */
    public function fetch(): \Iterator;

    /**
     * Updates the identifiers mapping.
     *
     * @param IdentifiersMapping $identifiersMapping
     */
    public function updateIdentifiersMapping(IdentifiersMapping $identifiersMapping): void;

    /**
     * @param string $subscriptionId
     */
    public function unsubscribe(string $subscriptionId): void;

    /**
     * @param string $familyCode
     *
     * @return AttributesMappingResponse
     */
    public function getAttributesMapping(string $familyCode): AttributesMappingResponse;

    /**
     * @param string $familyCode
     * @param array $attributesMapping
     */
    public function updateAttributesMapping(string $familyCode, array $attributesMapping): void;

    /**
     * @param FamilyCode $familyCode
     * @param FranklinAttributeId $franklinAttributeId
     *
     * @return ReadAttributeOptionsMapping
     */
    public function getAttributeOptionsMapping(
        FamilyCode $familyCode,
        FranklinAttributeId $franklinAttributeId
    ): ReadAttributeOptionsMapping;

    /**
     * @param FamilyCode $familyCode
     * @param FranklinAttributeId $franklinAttributeId
     * @param WriteAttributeOptionsMapping $attributeOptionsMapping
     */
    public function saveAttributeOptionsMapping(
        FamilyCode $familyCode,
        FranklinAttributeId $franklinAttributeId,
        WriteAttributeOptionsMapping $attributeOptionsMapping
    ): void;
}
