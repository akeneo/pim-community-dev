<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\Statistics;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\AbstractApi;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Api\AuthenticatedApiInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\BadRequestException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\FranklinServerException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception\InvalidTokenException;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\ValueObject\CreditsUsageStatistics;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Symfony\Component\HttpFoundation\Response;

class StatisticsWebService extends AbstractApi implements AuthenticatedApiInterface
{
    public function getCreditsUsageStatistics(): CreditsUsageStatistics
    {
        $route = $this->uriGenerator->generate('/api/stats');

        try {
            $response = $this->httpClient->request('GET', $route);
        } catch (ServerException $e) {
            throw new FranklinServerException('Something went wrong while fetching credits usage statistics');
        } catch (ClientException $e) {
            if (Response::HTTP_UNAUTHORIZED === $e->getCode()) {
                throw new InvalidTokenException();
            }

            throw new BadRequestException('Something went wrong while fetching credits usage statistics');
        }

        $content = json_decode($response->getBody()->getContents(), true);

        if (null === $content || !array_key_exists('stats', $content)) {
            throw new FranklinServerException('Response data incorrect! No "stats" key found');
        }

        return new CreditsUsageStatistics($content['stats']);
    }
}
