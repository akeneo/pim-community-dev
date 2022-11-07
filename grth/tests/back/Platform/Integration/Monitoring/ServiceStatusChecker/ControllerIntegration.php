<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Platform\Integration\Monitoring\ServiceStatusChecker;

use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\Controller;
use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\ElasticsearchChecker;
use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\FileStorageChecker;
use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\MysqlChecker;
use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\SmtpChecker;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;

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

        $smtpCheckerKo = new SmtpChecker(
            self::getContainer()->get('mailer.transport_factory.smtp'),
            'smtp://foo.bar',
            $this->createMock(LoggerInterface::class)
        );

        $this->controller = new Controller(
            self::getContainer()->get(MysqlChecker::class),
            self::getContainer()->get(ElasticsearchChecker::class),
            self::getContainer()->get(FileStorageChecker::class),
            $smtpCheckerKo,
            self::getContainer()->get('akeneo_monitoring.status_checker.pub_sub'),
            self::getContainer()->get('logger'),
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
                    'message' => 'Unable to ping the mailer transport: "Connection could not be established with host "ssl://foo.bar:465": stream_socket_client(): php_network_getaddresses: getaddrinfo failed: No address associated with hostname".',
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
                    'message' => 'Unable to ping the mailer transport: "Connection could not be established with host "ssl://foo.bar:465": stream_socket_client(): php_network_getaddresses: getaddrinfo failed: No address associated with hostname".',
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
        $mysqlCheckerKo = new MysqlChecker($mockConnection, $this->createMock(LoggerInterface::class));

        $this->controller = new Controller(
            $mysqlCheckerKo,
            self::getContainer()->get(ElasticsearchChecker::class),
            self::getContainer()->get(FileStorageChecker::class),
            self::getContainer()->get(SmtpChecker::class),
            self::getContainer()->get('akeneo_monitoring.status_checker.pub_sub'),
            self::getContainer()->get('logger'),
            'my_auth_token'
        );

        $expectedContent = [
            'service_status' => [
                'mysql' => [
                    'ok' => false,
                    'optional' => false,
                    'message' => 'MySQL exception: "mock message".',
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
