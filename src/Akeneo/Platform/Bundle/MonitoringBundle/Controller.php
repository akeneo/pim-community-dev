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

namespace Akeneo\Platform\Bundle\MonitoringBundle\ServiceStatus;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Send back services status in JSON format.
 *
 * @author Benoit Jacquemont <benoit@akeneo.com>
 */
// TODO: Check responder, cf PHP Bulgaria
final class Controller
{
    /** @var MysqlChecker */
    private $mysqlChecker;

    /** @var ElasticsearchChecker */
    private $esChecker;

    /** @var FileStorageChecker */
    private $fileStorageChecker;

    public function __construct(
        MysqlStatusChecker $mysqlChecker,
        ElasticsearchStatusChecker $esChecker,
        FileStorageStatusChecker $fileStorageChecker
    ) {
        $this->mysqlChecker = $mysqlChecker;
        $this->esChecker = $esChecker;
        $this->fileStorageChecker = $fileStorageChecker;
    }

    public function getAction(): JsonResponse
    {
        $mysqlStatus = $this->mysqlChecker->status();
        $esStatus = $this->esChecker->status();
        $fileStorageStatus = $this->fileStorageChecker->status();

        $responseStatus = Response::HTTP_OK;

        if (!$mysqlStatus->isOk() ||
            !$esStatus->isOk() ||
            !$fileStorageStatus->isOk()
        ) {
            $responseStatus = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return new JsonResponse(
            [
                'service_status' => [
                    'mysql' => [
                            'ok' => $mysqlStatus->isOk(),
                            'message' => $mysqlStatus->getMessage()
                    ],
                    'elasticsearch' => [
                            'ok' => $esStatus->isOk(),
                            'message' => $esStatus->getMessage()
                    ],
                    'file_storage' => [
                            'ok' => $fileStorageStatus->isOk(),
                            'message' => $fileStorageStatus->getMessage()
                    ]
                ]
            ],
            $responseStatus
        );
    }
}
