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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\DataProvider;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception\ProductSubscriptionException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductSubscriptionResponseCollection;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Write\ProductSubscriptionRequest;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
interface SubscriptionProviderInterface
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
     * @param string $subscriptionId
     */
    public function unsubscribe(string $subscriptionId): void;

    /**
     * @param ProductSubscriptionRequest[] $requests
     *
     * @throws ProductSubscriptionException
     *
     * @return ProductSubscriptionResponseCollection
     */
    public function bulkSubscribe(array $requests): ProductSubscriptionResponseCollection;

    /**
     * @param \DateTime|null $updatedSince
     *
     * @throws ProductSubscriptionException
     *
     * @return \Iterator
     */
    public function fetch(\DateTime $updatedSince = null): \Iterator;

    /**
     * @param string $subscriptionId
     * @param FamilyInterface $family
     *
     * @throws ProductSubscriptionException
     */
    public function updateFamilyInfos(string $subscriptionId, FamilyInterface $family): void;
}
