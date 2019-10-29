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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\Subscription;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\AbstractApi;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\ApiResponse;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\AuthenticatedApiInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\BadRequestException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\FranklinServerException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\InsufficientCreditsException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\InvalidTokenException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\UnableToConnectToFranklinException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\SubscriptionCollection;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\WarningCollection;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Concrete implementation of subscription web service.
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class SubscriptionWebService extends AbstractApi implements AuthenticatedApiInterface
{
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

            return new ApiResponse(new SubscriptionCollection($content), new WarningCollection($content));
        } catch (ConnectException $e) {
            $this->logger->error('Cannot connect to Ask Franklin to subscribe a product', [
                'exception' => $e->getMessage(),
                'route' => $route,
            ]);
            throw new UnableToConnectToFranklinException();
        } catch (ServerException | FranklinServerException $e) {
            $this->logger->error('Something went wrong on Ask Franklin side during product subscription', [
                'exception' => $e->getMessage(),
                'route' => $route,
                'request_body' => $request->toFormParams(),
            ]);
            throw new FranklinServerException(sprintf(
                'Something went wrong on Ask Franklin side during product subscription: %s',
                $e->getMessage()
            ));
        } catch (ClientException $e) {
            if (Response::HTTP_PAYMENT_REQUIRED === $e->getCode()) {
                $this->logger->warning('Insufficient credits to subscribe products to Ask Franklin', [
                    'exception' => $e->getMessage(),
                    'route' => $route,
                ]);
                throw new InsufficientCreditsException();
            }
            if (Response::HTTP_UNAUTHORIZED === $e->getCode()) {
                $this->logger->warning('Invalid token to subscribe products to Ask Franklin', [
                    'exception' => $e->getMessage(),
                    'route' => $route,
                ]);
                throw new InvalidTokenException();
            }

            $this->logger->error('Invalid product subscription request sent to Ask Franklin', [
                'exception' => $e->getMessage(),
                'route' => $route,
                'request_body' => $request->toFormParams(),
            ]);
            throw new BadRequestException(sprintf(
                'Something went wrong during product subscription: %s',
                $e->getMessage()
            ));
        }
    }

    public function fetchProducts(string $uri = null, \DateTime $updatedSince = null): SubscriptionsCollection
    {
        if (null === $uri) {
            $dateParam = (null === $updatedSince) ? 'yesterday' : $updatedSince->format('Y-m-d H:i:s');
            $route = $this->uriGenerator->generate(
                sprintf('/api/subscriptions/updated-since/%s', $dateParam)
            );
        } else {
            $route = $this->uriGenerator->getBaseUri() . $uri;
        }

        try {
            $response = $this->httpClient->request('GET', $route);

            return new SubscriptionsCollection($this, json_decode($response->getBody()->getContents(), true));
        } catch (ConnectException $e) {
            $this->logger->error('Cannot connect to Ask Franklin to fetch product data', [
                'exception' => $e->getMessage(),
                'route' => $route,
            ]);
            throw new UnableToConnectToFranklinException();
        } catch (ServerException $e) {
            $this->logger->error('Something went wrong on Ask Franklin side during product data fetch', [
                'exception' => $e->getMessage(),
                'route' => $route,
            ]);
            throw new FranklinServerException(
                sprintf('Something went wrong on Franklin side during product data fetch: %s.', $e->getMessage())
            );
        } catch (ClientException $e) {
            if (Response::HTTP_UNAUTHORIZED === $e->getCode()) {
                $this->logger->warning('Invalid token to fetch product data', [
                    'exception' => $e->getMessage(),
                    'route' => $route,
                ]);
                throw new InvalidTokenException();
            }

            $this->logger->error('Invalid fetch product request sent to Ask Franklin', [
                'exception' => $e->getMessage(),
                'route' => $route,
            ]);
            throw new BadRequestException(
                sprintf('Something went wrong during product data fetch: %s.', $e->getMessage())
            );
        }
    }

    public function unsubscribeProduct(string $subscriptionId): void
    {
        $route = $this->uriGenerator->generate(
            sprintf('/api/subscriptions/%s', $subscriptionId)
        );

        try {
            $this->httpClient->request('DELETE', $route);
        } catch (ConnectException $e) {
            $this->logger->error('Cannot connect to Ask Franklin to unsubscribe a product', [
                'exception' => $e->getMessage(),
                'route' => $route,
            ]);
            throw new UnableToConnectToFranklinException();
        } catch (ServerException $e) {
            $this->logger->error('Something went wrong on Ask Franklin side during product unsubscription', [
                'exception' => $e->getMessage(),
                'route' => $route,
            ]);
            throw new FranklinServerException(
                sprintf('Something went wrong on Franklin side during product unsubscription: %s', $e->getMessage())
            );
        } catch (ClientException $e) {
            if (Response::HTTP_UNAUTHORIZED === $e->getCode()) {
                $this->logger->warning('Invalid token to unsubscribe products to Ask Franklin', [
                    'exception' => $e->getMessage(),
                    'route' => $route,
                ]);
                throw new InvalidTokenException();
            }

            $this->logger->error('Invalid product unsubscription request sent to Ask Franklin', [
                'exception' => $e->getMessage(),
                'route' => $route,
            ]);
            throw new BadRequestException(
                sprintf('Something went wrong during product unsubscription: %s', $e->getMessage())
            );
        }
    }

    public function updateFamilyInfos(string $subscriptionId, array $familyInfos): void
    {
        $route = $this->uriGenerator->generate(
            sprintf('/api/subscriptions/%s/family', $subscriptionId)
        );

        try {
            $this->httpClient->request('PUT', $route, ['form_params' => $familyInfos]);
        } catch (ConnectException $e) {
            $this->logger->error('Cannot connect to Ask Franklin to update family infos', [
                'exception' => $e->getMessage(),
                'route' => $route,
            ]);
            throw new UnableToConnectToFranklinException();
        } catch (ServerException $e) {
            $this->logger->error('Something went wrong on Ask Franklin side during family infos update', [
                'exception' => $e->getMessage(),
                'route' => $route,
                'request_body' => $familyInfos,
            ]);
            throw new FranklinServerException(
                sprintf('Something went wrong on Franklin side during family infos update: %s', $e->getMessage())
            );
        } catch (ClientException $e) {
            if (Response::HTTP_UNAUTHORIZED === $e->getCode()) {
                $this->logger->warning('Invalid token to update family infos', [
                    'exception' => $e->getMessage(),
                    'route' => $route,
                ]);
                throw new InvalidTokenException();
            }

            $this->logger->error('Invalid family infos update request sent to Ask Franklin', [
                'exception' => $e->getMessage(),
                'route' => $route,
                'request_body' => $familyInfos,
            ]);
            throw new BadRequestException(
                sprintf('Something went wrong during family infos update: %s', $e->getMessage())
            );
        }
    }
}
