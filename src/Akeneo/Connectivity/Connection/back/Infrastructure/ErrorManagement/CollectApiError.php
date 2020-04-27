<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\BusinessError;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Persistence\Repository\BusinessErrorRepository;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Infrastructure\ConnectionContext;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class CollectApiError
{
    /** @var BusinessErrorRepository */
    private $repository;

    /** @var ConnectionContext */
    private $connectionContext;

    /** @var ExtractErrorsFromHttpException */
    private $extractErrorsFromHttpException;

    private $errors = [];

    public function __construct(
        ConnectionContext $connectionContext,
        BusinessErrorRepository $repository,
        ExtractErrorsFromHttpException $extractErrorsFromHttpException
    ) {
        $this->repository = $repository;
        $this->connectionContext = $connectionContext;
        $this->extractErrorsFromHttpException = $extractErrorsFromHttpException;
    }

    public function collectFromHttpException(HttpException $httpException): void
    {
        $errors = $this->extractErrorsFromHttpException->extractAll($httpException);
        $this->collect($errors);
    }

    public function save(): void
    {
        if (0 === count($this->errors)) {
            return;
        }

        $this->repository->bulkInsert($this->errors);
    }

    /**
     * @param string[] $errors
     */
    private function collect(array $errors): void
    {
        $connection = $this->connectionContext->getConnection();
        if (null === $connection) {
            return;
        }

        if (false === $this->connectionContext->isCollectable() || FlowType::DATA_SOURCE !== (string) $connection->flowType()) {
            return;
        }

        $this->errors = array_merge(
            $this->errors,
            array_map(function (string $error) use ($connection) {
                return new BusinessError($connection->code(), $error);
            }, $errors)
        );
    }
}
