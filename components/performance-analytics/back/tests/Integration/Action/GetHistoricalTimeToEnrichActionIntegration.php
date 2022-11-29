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
    public function testItReturnsHistoricalTimeToEnrichByMonth(): void
    {
        $response = $this->launchQuery('2022-01-01', '2022-06-30', 'month');
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame('application/json', $response->headers->get('Content-type'));

        $json = json_decode($response->getContent(), true);

        self::assertCount(6, $json);
        self::assertArrayHasKey('period', $json[0]);
        self::assertArrayHasKey('value', $json[0]);
        self::assertSame('2022-01', $json[0]['period']);
    }

    public function testItReturnsHistoricalTimeToEnrichByWeek(): void
    {
        $response = $this->launchQuery('2022-09-01', '2022-09-30', 'week');
        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        self::assertSame('application/json', $response->headers->get('Content-type'));

        $json = json_decode($response->getContent(), true);

        self::assertCount(5, $json);
        self::assertArrayHasKey('period', $json[0]);
        self::assertArrayHasKey('value', $json[0]);
    }

    /**
     * @dataProvider generateWrongParameters
     */
    public function testItReturnsBadRequestWhenParametersAreWrong(array $parameters): void
    {
        $this->client->request(
            Request::METHOD_GET,
            $this->router->generate('pimee_performance_analytics_historical_average_tte'),
            $parameters,
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json',
            ],
        );
        self::assertSame(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function generateWrongParameters(): array
    {
        return [
            [['start_date' => 'toto', 'end_date' => '2022-09-30', 'period_type' => 'day']],
            [['start_date' => '2022-09-01', 'end_date' => 'toto', 'period_type' => 'day']],
            [['start_date' => '2022-09-01', 'end_date' => '2022-09-30', 'period_type' => 'unknown']],
            [['end_date' => '2022-09-30', 'period_type' => 'day']],
            [['start_date' => '2022-09-01', 'period_type' => 'day']],
            [['start_date' => '2022-09-01', 'end_date' => '2022-09-30']],
            [['start_date' => '2022-09-30', 'end_date' => '2022-09-29', 'period_type' => 'day']],
            [['start_date' => '2022-09-30', 'end_date' => '2022-10-01', 'period_type' => 'day', 'channels' => 'mobile,']],
            [['start_date' => '2022-09-30', 'end_date' => '2022-10-01', 'period_type' => 'day', 'channels' => []]],
        ];
    }

    private function launchQuery(string $startDate, string $endDate, string $periodType): Response
    {
        $this->client->request(
            Request::METHOD_GET,
            $this->router->generate('pimee_performance_analytics_historical_average_tte'),
            [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'period_type' => $periodType,
            ],
            [],
            [
                'HTTP_X-Requested-With' => 'XMLHttpRequest',
                'CONTENT_TYPE' => 'application/json',
            ],
        );

        return $this->client->getResponse();
    }
}
