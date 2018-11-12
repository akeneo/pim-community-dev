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

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\AbstractApi;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\ApiResponse;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exception\BadRequestException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exception\FranklinServerException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exception\InsufficientCreditsException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exception\InvalidTokenException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\SubscriptionCollection;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Concrete implementation of subscription web service.
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class SubscriptionWebservice extends AbstractApi implements SubscriptionApiInterface
{
    /**
     * {@inheritdoc}
     */
    public function subscribe(RequestCollection $request): ApiResponse
    {
        $route = $this->uriGenerator->generate('/api/subscriptions');

        try {
            $response = $this->httpClient->request('POST', $route, [
                'form_params' => $request->toFormParams(),
            ]);

            $content = json_decode($response->getBody()->getContents(), true);
            if (null === $content) {
                throw new FranklinServerException('Empty response');
            }

            return new ApiResponse($response->getStatusCode(), new SubscriptionCollection($content));
        } catch (ServerException | FranklinServerException $e) {
            throw new FranklinServerException(sprintf(
                'Something went wrong on Franklin side during product subscription: %s',
                $e->getMessage()
            ));
        } catch (ClientException $e) {
            if (Response::HTTP_PAYMENT_REQUIRED === $e->getCode()) {
                throw new InsufficientCreditsException('Not enough credits on Franklin to subscribe');
            }
            if (Response::HTTP_FORBIDDEN === $e->getCode()) {
                throw new InvalidTokenException('The Franklin token is missing or invalid');
            }

            throw new BadRequestException(sprintf(
                'Something went wrong during product subscription: ',
                $e->getMessage()
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fetchProducts(string $uri = null): SubscriptionsCollection
    {
        if (null === $uri) {
            $dateParam = 'yesterday';
            $route = $this->uriGenerator->generate(
                sprintf('/api/subscriptions/updated-since/%s', $dateParam)
            );
        } else {
            $route = $this->uriGenerator->getBaseUri() . $uri;
        }

        try {
            $response = $this->httpClient->request('GET', $route);

            return new SubscriptionsCollection($this, json_decode($response->getBody()->getContents(), true));
        } catch (ServerException $e) {
            throw new FranklinServerException(
                sprintf('Something went wrong on Franklin side during product subscription: %s.', $e->getMessage())
            );
        } catch (ClientException $e) {
            if (Response::HTTP_PAYMENT_REQUIRED === $e->getCode()) {
                throw new InsufficientCreditsException('Not enough credits on Franklin to subscribe.');
            }
            if (Response::HTTP_FORBIDDEN === $e->getCode()) {
                throw new InvalidTokenException('The Franklin token is missing or invalid.');
            }

            throw new BadRequestException(
                sprintf('Something went wrong during product subscription: %s.', $e->getMessage())
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function unsubscribeProduct(string $subscriptionId): void
    {
        $route = $this->uriGenerator->generate(
            sprintf('/api/subscriptions/%s', $subscriptionId)
        );

        try {
            $this->httpClient->request('DELETE', $route);
        } catch (ServerException $e) {
            throw new FranklinServerException(
                sprintf('Something went wrong on Franklin side during product subscription: %s', $e->getMessage())
            );
        } catch (ClientException $e) {
            if (Response::HTTP_FORBIDDEN === $e->getCode()) {
                throw new InvalidTokenException('The Franklin token is missing or invalid');
            }

            throw new BadRequestException(
                sprintf('Something went wrong during product subscription: %s', $e->getMessage())
            );
        }
    }
}
