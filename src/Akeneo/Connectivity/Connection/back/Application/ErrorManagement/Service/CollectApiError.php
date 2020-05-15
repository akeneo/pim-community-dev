<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\ErrorManagement\Service;

use Akeneo\Connectivity\Connection\Application\ConnectionContextInterface;
use Akeneo\Connectivity\Connection\Application\ErrorManagement\Command\UpdateConnectionErrorCountCommand;
use Akeneo\Connectivity\Connection\Application\ErrorManagement\Command\UpdateConnectionErrorCountHandler;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\ApiErrorInterface;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\HourlyErrorCount;
use Akeneo\Connectivity\Connection\Domain\ValueObject\HourlyInterval;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\ErrorTypes;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Persistence\Repository\BusinessErrorRepository;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CollectApiError
{
    /** @var BusinessErrorRepository */
    private $repository;

    /** @var ConnectionContextInterface */
    private $connectionContext;

    /** @var ExtractErrorsFromHttpExceptionInterface */
    private $extractErrorsFromHttpException;

    /** @var UpdateConnectionErrorCountHandler */
    private $updateErrorCountHandler;

    /** @var array */
    private $errors;

    /** @var int */
    private $globalErrorCount;

    public function __construct(
        ConnectionContextInterface $connectionContext,
        BusinessErrorRepository $repository,
        ExtractErrorsFromHttpExceptionInterface $extractErrorsFromHttpException,
        UpdateConnectionErrorCountHandler $updateErrorCountHandler
    ) {
        $this->repository = $repository;
        $this->connectionContext = $connectionContext;
        $this->extractErrorsFromHttpException = $extractErrorsFromHttpException;
        $this->updateErrorCountHandler = $updateErrorCountHandler;
        $this->errors = array_fill_keys(ErrorTypes::getAll(), []);
        $this->globalErrorCount = 0;
    }

    public function collectFromHttpException(HttpException $httpException): void
    {
        $connection = $this->connectionContext->getConnection();
        if (null === $connection) {
            return;
        }

        if (false === $this->connectionContext->isCollectable() || FlowType::DATA_SOURCE !== (string) $connection->flowType()) {
            return;
        }
        $errors = $this->extractErrorsFromHttpException->extractAll($httpException);
        $this->collect($errors);
    }

    public function flush(): void
    {
        if (0 === $this->globalErrorCount) {
            return;
        }

        $connection = $this->connectionContext->getConnection();
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $errorCounts = [];
        foreach ($this->errors as $errorType => $errors) {
            $errorCounts[] = new HourlyErrorCount(
                (string) $connection->code(),
                HourlyInterval::createFromDateTime($now),
                count($errors),
                $errorType
            );
        }
        $command = new UpdateConnectionErrorCountCommand($errorCounts);
        $this->updateErrorCountHandler->handle($command);

        $this->repository->bulkInsert($connection->code(), $this->errors[ErrorTypes::BUSINESS]);
    }

    /**
     * @param ApiErrorInterface[] $errors
     */
    private function collect(array $errors): void
    {
        foreach ($errors as $error) {
            $this->errors[(string) $error->type()][] = $error;
            $this->globalErrorCount++;
        }
    }
}
