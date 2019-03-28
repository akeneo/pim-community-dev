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

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Model\Read\Family;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception\ProductSubscriptionException;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read\ProductSubscriptionResponseCollection;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Write\ProductSubscriptionRequest;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\ValueObject\SubscriptionId;

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
     * @param SubscriptionId $subscriptionId
     */
    public function unsubscribe(SubscriptionId $subscriptionId): void;

    /**
     * @param ProductSubscriptionRequest[] $requests
     *
     * @throws ProductSubscriptionException
     *
     * @return ProductSubscriptionResponseCollection
     */
    public function bulkSubscribe(array $requests): ProductSubscriptionResponseCollection;

    /**
     * @param \DateTime $updatedSince
     *
     * @throws ProductSubscriptionException
     *
     * @return \Iterator
     */
    public function fetch(\DateTime $updatedSince): \Iterator;

    /**
     * @param SubscriptionId $subscriptionId
     * @param Family $family
     *
     * @throws ProductSubscriptionException
     */
    public function updateFamilyInfos(SubscriptionId $subscriptionId, Family $family): void;
}
