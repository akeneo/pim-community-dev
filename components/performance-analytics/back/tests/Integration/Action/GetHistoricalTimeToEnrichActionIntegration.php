<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\PerformanceAnalytics\Integration\Action;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class GetHistoricalTimeToEnrichActionIntegration extends ActionIntegrationTestCase
{
    public function testItReturnsHistoricalTimeToEnrich(): void
    {
        $this->client->request(
            Request::METHOD_GET,
            $this->router->generate('pimee_performance_analytics_historical_average_tte'),
            [
                'start_date' => '2022-09-01',
                'end_date' => '2022-09-30',
                'period_type' => 'week',
            ]
        );

        $response = $this->client->getResponse();
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame('application/json', $response->headers->get('Content-type'));

        $json = json_decode($response->getContent(), true);

        self::assertCount(5, $json);
        self::assertArrayHasKey('period', $json[0]);
        self::assertArrayHasKey('value', $json[0]);
    }
}
