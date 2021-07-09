<?php
declare(strict_types=1);

namespace AkeneoTestEnterprise\Platform\Integration\Monitoring\ServiceStatusChecker;

use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\Controller;
use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\ElasticsearchChecker;
use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\FileStorageChecker;
use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\MysqlChecker;
use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\SmtpChecker;
use Akeneo\Platform\Component\Monitoring\Exception\StatusCheckException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ControllerIntegration extends KernelTestCase
{
    private Controller $controller;
    /** @var MockObject|Request  */
    private $request;

    public function setUp(): void
    {
        self::bootKernel();

        $this->request = $this->createMock(Request::class);
        $this->request->headers = new ParameterBag([
            'X-AUTH-TOKEN' => 'my_auth_token',
        ]);

        $smtpCheckerKo = new SmtpChecker($this->createMock(\Swift_Transport::class));

        $this->controller = new Controller(
            self::$container->get(MysqlChecker::class),
            self::$container->get(ElasticsearchChecker::class),
            self::$container->get(FileStorageChecker::class),
            $smtpCheckerKo,
            self::$container->get('akeneo_monitoring.status_checker.pub_sub'),
            self::$container->get('logger'),
            'my_auth_token'
        );
    }

    public function test_ok_without_specifying_optional_services_failures(): void
    {
        $this->request->query = new ParameterBag();

        $response = $this->controller->getAction($this->request);

        $expectedContent = [
            'service_status' => [
                'mysql' => [
                    'ok' => true,
                    'optional' => false,
                    'message' => 'OK',
                ],
                'elasticsearch' => [
                    'ok' => true,
                    'optional' => false,
                    'message' => 'OK',
                ],
                'file_storage' => [
                    'ok' => true,
                    'optional' => false,
                    'message' => 'OK',
                ],
                'smtp' => [
                    'ok' => false,
                    'optional' => true,
                    'message' => 'Unable to ping the mailer transport.',
                ],
                'pub_sub' => [
                    'ok' => true,
                    'optional' => false,
                    'message' => 'OK',
                ],
            ],
        ];

        Assert::assertSame($expectedContent, json_decode($response->getContent(), true));
        Assert::assertSame(Response::HTTP_OK, $response->getStatusCode());
    }

    public function test_ko_when_reject_optional_services_failures(): void
    {
        $this->request->query = new ParameterBag([
            'fail_on_optional_services' => 'any_value',
        ]);

        $expectedContent = [
            'service_status' => [
                'mysql' => [
                    'ok' => true,
                    'optional' => false,
                    'message' => 'OK',
                ],
                'elasticsearch' => [
                    'ok' => true,
                    'optional' => false,
                    'message' => 'OK',
                ],
                'file_storage' => [
                    'ok' => true,
                    'optional' => false,
                    'message' => 'OK',
                ],
                'smtp' => [
                    'ok' => false,
                    'optional' => true,
                    'message' => 'Unable to ping the mailer transport.',
                ],
                'pub_sub' => [
                    'ok' => true,
                    'optional' => false,
                    'message' => 'OK',
                ],
            ],
        ];

        $response = $this->controller->getAction($this->request);

        Assert::assertSame($expectedContent, json_decode($response->getContent(), true));
        Assert::assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }

    public function test_ko_with_required_services_failures(): void
    {
        $this->request->query = new ParameterBag();

        $mockConnection = $this->createMock(Connection::class);
        $mockConnection->method('executeQuery')->willThrowException(new DBALException('mock message'));
        $mysqlCheckerKo = new MysqlChecker($mockConnection);

        $this->controller = new Controller(
            $mysqlCheckerKo,
            self::$container->get(ElasticsearchChecker::class),
            self::$container->get(FileStorageChecker::class),
            self::$container->get(SmtpChecker::class),
            self::$container->get('akeneo_monitoring.status_checker.pub_sub'),
            self::$container->get('logger'),
            'my_auth_token'
        );

        $expectedContent = [
            'service_status' => [
                'mysql' => [
                    'ok' => false,
                    'optional' => false,
                    'message' => 'Unable to request the database: "mock message".',
                ],
                'elasticsearch' => [
                    'ok' => true,
                    'optional' => false,
                    'message' => 'OK',
                ],
                'file_storage' => [
                    'ok' => true,
                    'optional' => false,
                    'message' => 'OK',
                ],
                'smtp' => [
                    'ok' => true,
                    'optional' => true,
                    'message' => 'OK',
                ],
                'pub_sub' => [
                    'ok' => true,
                    'optional' => false,
                    'message' => 'OK',
                ],
            ],
        ];

        $response = $this->controller->getAction($this->request);

        Assert::assertSame($expectedContent, json_decode($response->getContent(), true));
        Assert::assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }
}
