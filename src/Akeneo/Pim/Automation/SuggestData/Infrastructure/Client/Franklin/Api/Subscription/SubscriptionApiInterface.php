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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\Subscription;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\ApiResponse;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Api\AuthenticatedApi;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Exception\BadRequestException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Exception\FranklinServerException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Exception\InsufficientCreditsException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Franklin\Exception\InvalidTokenException;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
interface SubscriptionApiInterface extends AuthenticatedApi
{
    /**
     * @param RequestCollection $request
     *
     * @throws BadRequestException
     * @throws InsufficientCreditsException
     * @throws InvalidTokenException
     * @throws FranklinServerException
     *
     * @return ApiResponse
     */
    public function subscribe(RequestCollection $request): ApiResponse;

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

    /**
     * @param string $subscriptionId
     * @param array $familyInfos
     *
     * @throws BadRequestException
     * @throws InvalidTokenException
     * @throws FranklinServerException
     */
    public function updateFamilyInfos(string $subscriptionId, array $familyInfos): void;
}
