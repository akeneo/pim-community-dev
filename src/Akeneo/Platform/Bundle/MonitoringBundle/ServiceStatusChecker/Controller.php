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

    public function __construct(
        MysqlChecker $mysqlChecker,
        ElasticsearchChecker $elasticsearchChecker,
        FileStorageChecker $fileStorageChecker,
        SmtpChecker $smtpChecker,
        PubSubStatusCheckerInterface $pubSubStatusChecker,
        string $authenticationToken
    ) {
        $this->mysqlChecker = $mysqlChecker;
        $this->elasticsearchChecker = $elasticsearchChecker;
        $this->fileStorageChecker = $fileStorageChecker;
        $this->authenticationToken = $authenticationToken;
        $this->pubSubStatusChecker = $pubSubStatusChecker;
        $this->smtpChecker = $smtpChecker;
    }

    public function getAction(Request $request): JsonResponse
    {
        $authenticationToken = $request->headers->get('X-AUTH-TOKEN', null);

        if (null === $authenticationToken || $authenticationToken !== $this->authenticationToken) {
            throw new AccessDeniedHttpException();
        }

        $mysqlStatus = $this->mysqlChecker->status();
        $esStatus = $this->elasticsearchChecker->status();
        $fileStorageStatus = $this->fileStorageChecker->status();
        $smtpStatus = $this->smtpChecker->status();
        $pubSubStatus = $this->pubSubStatusChecker->status();

        $responseStatus = Response::HTTP_OK;

        if (
            !$mysqlStatus->isOk()
            || !$esStatus->isOk()
            || !$fileStorageStatus->isOk()
            || !$smtpStatus->isOk()
            || !$pubSubStatus->isOk()
        ) {
            $responseStatus = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return new JsonResponse(
            [
                'service_status' => [
                    'mysql' => [
                        'ok' => $mysqlStatus->isOk(),
                        'message' => $mysqlStatus->getMessage(),
                    ],
                    'elasticsearch' => [
                        'ok' => $esStatus->isOk(),
                        'message' => $esStatus->getMessage(),
                    ],
                    'file_storage' => [
                        'ok' => $fileStorageStatus->isOk(),
                        'message' => $fileStorageStatus->getMessage(),
                    ],
                    'smtp' => [
                        'ok' => $smtpStatus->isOk(),
                        'message' => $smtpStatus->getMessage(),
                    ],
                    'pub_sub' => [
                        'ok' => $pubSubStatus->isOk(),
                        'message' => $pubSubStatus->getMessage(),
                    ],
                ],
            ],
            $responseStatus
        );
    }
}
