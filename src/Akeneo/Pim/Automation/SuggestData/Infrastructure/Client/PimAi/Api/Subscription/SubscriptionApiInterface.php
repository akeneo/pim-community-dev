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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Subscription;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\ApiResponse;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\AuthenticatedApi;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exception\BadRequestException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exception\FranklinServerException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exception\InsufficientCreditsException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exception\InvalidTokenException;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
interface SubscriptionApiInterface extends AuthenticatedApi
{
    /**
     * @param array $identifiers
     * @param int $trackerId
     * @param array $familyInfos
     *
     * @return ApiResponse
     */
    public function subscribeProduct(array $identifiers, int $trackerId, array $familyInfos): ApiResponse;

    /**
     * TODO: Rename this method. It does not fetch products.
     *
     * @param string|null $uri in case you have a pre-encoded uri
     *
     * @throws BadRequestException
     * @throws InsufficientCreditsException
     * @throws InvalidTokenException
     * @throws FranklinServerException
     *
     * @return SubscriptionsCollection
     */
    public function fetchProducts(string $uri = null): SubscriptionsCollection;

    /**
     * @param string $subscriptionId
     *
     * @throws BadRequestException
     * @throws InvalidTokenException
     * @throws FranklinServerException
     */
    public function unsubscribeProduct(string $subscriptionId): void;
}
