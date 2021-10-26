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

namespace Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker;

use Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatusChecker\PubSub\PubSubStatusCheckerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Send back services status in JSON format.
 *
 * @author Benoit Jacquemont <benoit@akeneo.com>
 */
final class Controller
{
    private MysqlChecker $mysqlChecker;
    private ElasticsearchChecker $elasticsearchChecker;
    private FileStorageChecker $fileStorageChecker;
    private string $authenticationToken;
    private PubSubStatusCheckerInterface $pubSubStatusChecker;
    private SmtpChecker $smtpChecker;

    private LoggerInterface $logger;

    public function __construct(
        MysqlChecker $mysqlChecker,
        ElasticsearchChecker $elasticsearchChecker,
        FileStorageChecker $fileStorageChecker,
        SmtpChecker $smtpChecker,
        PubSubStatusCheckerInterface $pubSubStatusChecker,
        LoggerInterface $logger,
        string $authenticationToken
    ) {
        $this->mysqlChecker = $mysqlChecker;
        $this->elasticsearchChecker = $elasticsearchChecker;
        $this->fileStorageChecker = $fileStorageChecker;
        $this->pubSubStatusChecker = $pubSubStatusChecker;
        $this->smtpChecker = $smtpChecker;

        $this->logger = $logger;

        $this->authenticationToken = $authenticationToken;
    }

    public function getAction(Request $request): JsonResponse
    {
        $authenticationToken = $request->headers->get('X-AUTH-TOKEN');
        $failOnOptionalServices = $request->query->has('fail_on_optional_services');

        if (null === $authenticationToken || $authenticationToken !== $this->authenticationToken) {
            throw new AccessDeniedHttpException();
        }

        $mysqlStatus = $this->timedServiceCheck(function () {
            return $this->mysqlChecker->status();
        }, "Mysql");
        $esStatus = $this->timedServiceCheck(function () {
            return $this->elasticsearchChecker->status();
        }, "ElasticSearch");
        $fileStorageStatus = $this->timedServiceCheck(function () {
            return $this->fileStorageChecker->status();
        }, "FileStorage");
        $smtpStatus = $this->timedServiceCheck(function () {
            return $this->smtpChecker->status();
        }, "SmtpServer");
        $pubSubStatus = $this->timedServiceCheck(function () {
            return $this->pubSubStatusChecker->status();
        }, "PubSub");

        $responseContent = [
            'service_status' => [
                'mysql' => [
                    'ok' => $mysqlStatus->isOk(),
                    'optional' => false,
                    'message' => $mysqlStatus->getMessage(),
                ],
                'elasticsearch' => [
                    'ok' => $esStatus->isOk(),
                    'optional' => false,
                    'message' => $esStatus->getMessage(),
                ],
                'file_storage' => [
                    'ok' => $fileStorageStatus->isOk(),
                    'optional' => false,
                    'message' => $fileStorageStatus->getMessage(),
                ],
                'smtp' => [
                    'ok' => $smtpStatus->isOk(),
                    'optional' => true,
                    'message' => $smtpStatus->getMessage(),
                ],
                'pub_sub' => [
                    'ok' => $pubSubStatus->isOk(),
                    'optional' => false,
                    'message' => $pubSubStatus->getMessage(),
                ],
            ],
        ];

        $responseStatus = $this->isResponseSuccesful($responseContent['service_status'], $failOnOptionalServices) ?
            Response::HTTP_OK : Response::HTTP_INTERNAL_SERVER_ERROR;

        if (Response::HTTP_OK !== $responseStatus) {
            $this->logger->error("Status Check Error: " . json_encode($responseContent));
        }


        return new JsonResponse($responseContent, $responseStatus);
    }

    private function isResponseSuccesful(array $responseContent, bool $failOnOptionalServices): bool
    {
        foreach ($responseContent as $result) {
            if (!$result['ok'] && !$result['optional']) {
                return false;
            }
            if (!$result['ok'] && $failOnOptionalServices) {
                return false;
            }
        }

        return true;
    }

    public function timedServiceCheck(\Closure $serviceCheck, string $serviceCheckName): ServiceStatus
    {
        $startTime = new \DateTime();
        $serviceStatus = $serviceCheck();
        $endTime = new \DateTime();
        $durationSeconds = $endTime->getTimestamp() - $startTime->getTimestamp();
        if ($durationSeconds >= 10) {
            $this->logger->warning("{$serviceCheckName} service check performed in {$durationSeconds} s.");
        }
        return $serviceStatus;
    }
}
