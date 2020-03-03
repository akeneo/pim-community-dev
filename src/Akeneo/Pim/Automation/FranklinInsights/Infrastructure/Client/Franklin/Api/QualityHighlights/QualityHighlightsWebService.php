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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\QualityHighlights;

use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\Model\Write\AsyncRequest;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\AbstractApi;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\AuthenticatedApiInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\BadRequestException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\FranklinServerException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\InvalidTokenException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\QualityHighlightsMetrics;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Promise;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class QualityHighlightsWebService extends AbstractApi implements AuthenticatedApiInterface
{
    public function applyAttributes(array $attributes): void
    {
        $route = $this->uriGenerator->generate('/api/quality-highlights/structure/attributes');

        try {
            $this->httpClient->request('POST', $route, [
                'json' => $attributes,
            ]);
        } catch (ServerException $e) {
            throw new FranklinServerException(
                sprintf(
                    'Something went wrong on Ask Franklin side when sending attributes : %s',
                    $e->getMessage()
                )
            );
        } catch (ClientException $e) {
            if (Response::HTTP_UNAUTHORIZED === $e->getCode()) {
                throw new InvalidTokenException();
            }

            throw new BadRequestException(sprintf(
                'Something went wrong when sending attributes (bad request) : %s',
                $e->getMessage()
            ));
        }
    }

    /**
     * @param AsyncRequest[] $asyncRequests
     */
    public function applyAsyncAttributes(array $asyncRequests): void
    {
        $uri = $this->uriGenerator->generate('/api/quality-highlights/structure/attributes');

        $promises = [];
        foreach ($asyncRequests as $request) {
            $promise = $this->httpClient->requestAsync('POST', $uri, [
                'json' => ['attributes' => $request->getData()],
            ]);
            $promise->then($request->getOnFulfilled(), $request->getOnRejected());
            $promises[] = $promise;
        }

        try {
            Promise\settle($promises)->wait();
        } catch (\Exception $e) {
            $this->logger->error(sprintf(
                'Something went wrong on Ask Franklin side when sending attributes : %s',
                $e->getMessage()
            ));
        }
    }

    public function deleteAttribute(string $attributeCode): void
    {
        $route = $this->uriGenerator->generate(sprintf('/api/quality-highlights/structure/attributes/%s', $attributeCode));

        try {
            $this->httpClient->request('DELETE', $route);
        } catch (ServerException $e) {
            throw new FranklinServerException(
                sprintf(
                    'Something went wrong on Ask Franklin side when deleting an attribute : %s',
                    $e->getMessage()
                )
            );
        } catch (ClientException $e) {
            if (Response::HTTP_UNAUTHORIZED === $e->getCode()) {
                throw new InvalidTokenException();
            }

            throw new BadRequestException(sprintf(
                'Something went wrong when deleting an attribute (bad request) : %s',
                $e->getMessage()
            ));
        }
    }

    public function applyFamilies(array $families): void
    {
        $route = $this->uriGenerator->generate('/api/quality-highlights/structure/families');

        try {
            $this->httpClient->request('POST', $route, [
                'json' => ['families' => $families],
            ]);
        } catch (ServerException $e) {
            throw new FranklinServerException(
                sprintf(
                    'Something went wrong on Ask Franklin side when sending families : %s',
                    $e->getMessage()
                )
            );
        } catch (ClientException $e) {
            if (Response::HTTP_UNAUTHORIZED === $e->getCode()) {
                throw new InvalidTokenException();
            }

            throw new BadRequestException(sprintf(
                'Something went wrong when sending families (bad request) : %s',
                $e->getMessage()
            ));
        }
    }

    public function deleteFamily(string $familyCode): void
    {
        $route = $this->uriGenerator->generate(sprintf('/api/quality-highlights/structure/families/%s', $familyCode));

        try {
            $this->httpClient->request('DELETE', $route);
        } catch (ServerException $e) {
            throw new FranklinServerException(
                sprintf(
                    'Something went wrong on Ask Franklin side when deleting a family : %s',
                    $e->getMessage()
                )
            );
        } catch (ClientException $e) {
            if (Response::HTTP_UNAUTHORIZED === $e->getCode()) {
                throw new InvalidTokenException();
            }

            throw new BadRequestException(sprintf(
                'Something went wrong when deleting a family (bad request) : %s',
                $e->getMessage()
            ));
        }
    }

    /**
     * @param AsyncRequest[] $asyncRequests
     */
    public function applyAsyncProducts(array $asyncRequests): void
    {
        $uri = $this->uriGenerator->generate('/api/quality-highlights/data/products');

        $promises = [];
        foreach ($asyncRequests as $request) {
            $promise = $this->httpClient->requestAsync('POST', $uri, [
                'json' => ['products' => $request->getData()],
            ]);
            $promise->then($request->getOnFulfilled(), $request->getOnRejected());
            $promises[] = $promise;
        }

        try {
            Promise\settle($promises)->wait();
        } catch (\Exception $e) {
            $this->logger->error(sprintf(
                'Something went wrong on Ask Franklin side when sending products : %s',
                $e->getMessage()
            ));
        }
    }

    public function deleteProduct(int $productId): void
    {
        $route = $this->uriGenerator->generate(sprintf('/api/quality-highlights/data/products/%s', $productId));

        try {
            $this->httpClient->request('DELETE', $route);
        } catch (ServerException $e) {
            throw new FranklinServerException(
                sprintf(
                    'Something went wrong on Ask Franklin side when deleting a product : %s',
                    $e->getMessage()
                )
            );
        } catch (ClientException $e) {
            if (Response::HTTP_UNAUTHORIZED === $e->getCode()) {
                throw new InvalidTokenException();
            }

            throw new BadRequestException(sprintf(
                'Something went wrong when deleting a product (bad request) : %s',
                $e->getMessage()
            ));
        }
    }

    public function getMetrics(): QualityHighlightsMetrics
    {
        $route = $this->uriGenerator->generate('/api/quality-highlights');

        try {
            $response = $this->httpClient->request('GET', $route);
        } catch (ServerException $e) {
            throw new FranklinServerException('Something went wrong while fetching quality highlight metrics');
        } catch (ClientException $e) {
            if (Response::HTTP_UNAUTHORIZED === $e->getCode()) {
                throw new InvalidTokenException();
            }

            throw new BadRequestException('Something went wrong while fetching quality highlight metrics');
        }

        $metrics = json_decode($response->getBody()->getContents(), true);

        if (!is_array($metrics)) {
            throw new FranklinServerException('Response data incorrect when fetching quality highlight metrics');
        }

        return new QualityHighlightsMetrics($metrics);
    }
}
