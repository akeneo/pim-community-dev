<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\back\tests\EndToEnd\ErrorManagement;

use Akeneo\Connectivity\Connection\back\tests\EndToEnd\WebTestCase;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\BusinessError;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Persistence\Repository\BusinessErrorRepositoryInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode;
use Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\Persistence\ElasticsearchBusinessErrorRepository;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Response;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetConnectionBusinessErrorsEndToEnd extends WebTestCase
{
    public function test_it_gets_a_connection_business_errors(): void
    {
        $errors = [
            new BusinessError('{"message":"Error 1"}', new \DateTimeImmutable('2020-01-01T00:00:00+00:00')),
            new BusinessError('{"message":"Error 2"}', new \DateTimeImmutable('2020-01-07T00:00:00+00:00'))
        ];
        $this->insertBusinessErrors(new ConnectionCode('erp'), $errors);

        $expectedResult = [
            [
                'connection_code' => 'erp',
                'date_time' => '2020-01-07T00:00:00+00:00',
                'content' => ['message' => 'Error 2']
            ],
            [
                'connection_code' => 'erp',
                'date_time' => '2020-01-01T00:00:00+00:00',
                'content' => ['message' => 'Error 1']
            ],
        ];

        $this->authenticateAsAdmin();
        $this->client->request('GET', '/rest/connections/erp/business-errors', [
            'end_date' => '2020-01-07',
        ]);
        $result = \json_decode($this->client->getResponse()->getContent(), true, 512, JSON_THROW_ON_ERROR);

        Assert::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        Assert::assertEquals($expectedResult, $result);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * @param BusinessError[] $errors
     */
    private function insertBusinessErrors(ConnectionCode $connectionCode, array $errors): void
    {
        /** @var BusinessErrorRepositoryInterface */
        $repository = $this->get(ElasticsearchBusinessErrorRepository::class);
        $repository->bulkInsert($connectionCode, $errors);

        /** @var Client */
        $elasticsearchClient = $this->get('akeneo_connectivity.client.connection_error');
        $elasticsearchClient->refreshIndex();
    }
}
