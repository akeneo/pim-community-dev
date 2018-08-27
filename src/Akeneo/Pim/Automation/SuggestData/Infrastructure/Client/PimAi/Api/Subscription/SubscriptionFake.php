<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Subscription;

use Akeneo\Pim\Automation\SuggestData\Domain\Repository\ConfigurationRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\ApiResponse;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Authentication\AuthenticationApiInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exception\InsufficientCreditsException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exception\InvalidTokenException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\SubscriptionCollection;

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
    public function subscribeProduct(array $identifiers): ApiResponse
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

        $filename = sprintf('subscribe-%s-%s.json', key($identifiers), current($identifiers));

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
    public function fetchProducts(): ApiResponse
    {
        $filename = sprintf('fetch-%s.json', $this->lastFetchDate);

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
     * Fakes an expired token
     */
    public function expireToken(): void
    {
        $this->status = self::STATUS_EXPIRED_TOKEN;
    }

    /**
     * Fakes an empty credit
     */
    public function disableCredit(): void
    {
        $this->status = self::STATUS_INSUFFICIENT_CREDITS;
    }

    /**
     * Fakes a last fetch date
     * Could be a date or "yesterday" or "today"
     *
     * @param string $lastFetchDate
     */
    public function defineLastFetchDate(string $lastFetchDate): void
    {
        $this->lastFetchDate = $lastFetchDate;
    }
}
