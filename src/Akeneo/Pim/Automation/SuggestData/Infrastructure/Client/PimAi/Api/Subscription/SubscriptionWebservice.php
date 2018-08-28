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
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Client;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exception\BadRequestException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exception\InsufficientCreditsException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exception\InvalidTokenException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exception\PimAiServerException;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\UriGenerator;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\ValueObject\SubscriptionCollection;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Concrete implementation of subscription web service
 * 
 * @author Romain Monceau <romain@akeneo.com>
 */
class SubscriptionWebservice implements SubscriptionApiInterface
{
    /** @var UriGenerator */
    private $uriGenerator;

    /** @var Client */
    private $httpClient;

    /**
     * @param UriGenerator $uriGenerator
     * @param Client $httpClient
     */
    public function __construct(UriGenerator $uriGenerator, Client $httpClient)
    {
        $this->uriGenerator = $uriGenerator;
        $this->httpClient = $httpClient;
    }

    /**
     * {@inheritdoc}
     */
    public function subscribeProduct(array $identifiers): ApiResponse
    {
        $route = $this->uriGenerator->generate('/subscriptions');

        try {
            $response = $this->httpClient->request('POST', $route, [
                'form_params' => [$identifiers],
            ]);

            return new ApiResponse(
                $response->getStatusCode(),
                new SubscriptionCollection(json_decode($response->getBody()->getContents(), true))
            );
        } catch (ServerException $e) {
            throw new PimAiServerException(sprintf('Something went wrong on PIM.ai side during product subscription : ', $e->getMessage()));
        } catch (ClientException $e) {
            if ($e->getCode() === Response::HTTP_PAYMENT_REQUIRED) {
                throw new InsufficientCreditsException('Not enough credits on PIM.ai to subscribe');
            }
            if ($e->getCode() === Response::HTTP_FORBIDDEN) {
                throw new InvalidTokenException('The PIM.ai token is missing or invalid');
            }

            throw new BadRequestException(sprintf('Something went wrong during product subscription : ', $e->getMessage()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fetchProducts(): ApiResponse
    {
        $dateParam = 'yesterday';
        $route = $this->uriGenerator->generate(
            sprintf('/subscriptions/updated-since/%s', $dateParam)
        );

        try {
            $response = $this->httpClient->request('GET', $route);

            return new ApiResponse(
                $response->getStatusCode(),
                new SubscriptionCollection(json_decode($response->getBody()->getContents(), true))
            );
        } catch (ServerException $e) {
            throw new PimAiServerException(
                sprintf('Something went wrong on PIM.ai side during product subscription : ', $e->getMessage())
            );
        } catch (ClientException $e) {
            if ($e->getCode() === Response::HTTP_PAYMENT_REQUIRED) {
                throw new InsufficientCreditsException('Not enough credits on PIM.ai to subscribe');
            }
            if ($e->getCode() === Response::HTTP_FORBIDDEN) {
                throw new InvalidTokenException('The PIM.ai token is missing or invalid');
            }

            throw new BadRequestException(sprintf('Something went wrong during product subscription : ', $e->getMessage()));
        }
    }
}
