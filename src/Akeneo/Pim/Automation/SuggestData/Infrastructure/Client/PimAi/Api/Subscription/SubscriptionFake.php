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
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exception\InsufficientCreditsException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exception\InvalidTokenException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\SubscriptionCollection;

/**
 * Fake implementation for PIM.ai subscription.
 */
final class SubscriptionFake implements SubscriptionApiInterface
{
    /** @var string */
    private const STATUS_EXPIRED_TOKEN = 'expired_token';

    /** @var string */
    private const STATUS_INSUFFICIENT_CREDITS = 'insufficient_credits';

    /** @var string */
    private $status;

    /** @var string */
    private $lastFetchDate;

    /**
     * {@inheritdoc}
     */
    public function subscribeProduct(array $identifiers, int $trackerId, array $familyInfos): ApiResponse
    {
        switch ($this->status) {
            case self::STATUS_EXPIRED_TOKEN:
                throw new InvalidTokenException();
                break;
            case self::STATUS_INSUFFICIENT_CREDITS:
                throw new InsufficientCreditsException();
                break;
            default:
                break;
        }

        $filename = sprintf('subscriptions/post/%s-%s.json', key($identifiers), current($identifiers));

        return new ApiResponse(
            200,
            new SubscriptionCollection(
                json_decode(
                    file_get_contents(
                        sprintf(__DIR__ . '/../resources/%s', $filename)
                    ),
                    true
                )
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function fetchProducts(string $uri = null): SubscriptionsCollection
    {
        switch ($this->status) {
            case self::STATUS_EXPIRED_TOKEN:
                throw new InvalidTokenException();
                break;
            default:
                break;
        }

        $filename = sprintf('subscriptions/updated-since/%s.json', $this->lastFetchDate);

        return new SubscriptionsCollection(
            $this,
            json_decode(
                file_get_contents(
                    sprintf(__DIR__ . '/../resources/%s', $filename)
                ),
                true
            )
        );
    }

    /**
     * {@inheritdoc}
     *
     * Fake implementation just takes the subscription id
     * It does not return anything but can throw some exceptions
     */
    public function unsubscribeProduct(string $subscriptionId): void
    {
        switch ($this->status) {
            case self::STATUS_EXPIRED_TOKEN:
                throw new InvalidTokenException();
                break;
            default:
                break;
        }
    }

    /**
     * Fakes an expired token.
     */
    public function expireToken(): void
    {
        $this->status = self::STATUS_EXPIRED_TOKEN;
    }

    /**
     * Fakes an empty credit.
     */
    public function disableCredit(): void
    {
        $this->status = self::STATUS_INSUFFICIENT_CREDITS;
    }

    /**
     * Fakes a last fetch date
     * Could be a date or "yesterday" or "today".
     *
     * @param string $lastFetchDate
     */
    public function defineLastFetchDate(string $lastFetchDate): void
    {
        $this->lastFetchDate = $lastFetchDate;
    }

    /**
     * {@inheritdoc}
     */
    public function setToken(string $token): void
    {
    }
}
