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

use Akeneo\Pim\Automation\SuggestData\Domain\Exception\ProductSubscriptionException;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Model\Read\ProductSubscriptionResponse;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Model\Read\ProductSubscriptionResponseCollection;
use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Model\Write\ProductSubscriptionRequest;

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
     * @throws ProductSubscriptionException
     *
     * @return \Iterator
     */
    public function fetch(): \Iterator;
}
