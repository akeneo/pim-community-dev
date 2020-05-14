<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\ErrorManagement\Service;

use Akeneo\Connectivity\Connection\Application\ErrorManagement\Command\UpdateConnectionErrorCountCommand;
use Akeneo\Connectivity\Connection\Application\ErrorManagement\Command\UpdateConnectionErrorCountHandler;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\ErrorTypes;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\BusinessError;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\HourlyErrorCount;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\TechnicalError;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Persistence\Repository\BusinessErrorRepository;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Infrastructure\ConnectionContext;
use Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\ExtractErrorsFromHttpException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CollectApiErrorSpec extends ObjectBehavior
{
    public function let(
        ConnectionContext $connectionContext,
        BusinessErrorRepository $repository,
        ExtractErrorsFromHttpException $extractErrorsFromHttpException,
        UpdateConnectionErrorCountHandler $updateErrorCountHandler
    ): void {
        $this->beConstructedWith(
            $connectionContext,
            $repository,
            $extractErrorsFromHttpException,
            $updateErrorCountHandler
        );
    }

    public function it_collects_an_error_from_an_http_exception(
        $extractErrorsFromHttpException,
        $connectionContext,
        Connection $connection,
        $repository,
        $updateErrorCountHandler
    ): void {
        $exception = new HttpException(400);
        $connectionCode = new ConnectionCode('erp');
        $technicalError = new TechnicalError($connectionCode, '{"message":"technical error"}');
        $anotherTechError = new TechnicalError($connectionCode, '{"message":"Another technical error"}');
        $businessError = new BusinessError($connectionCode, '{"message":"business error"}');
        $anotherBusError = new BusinessError($connectionCode, '{"message":"another business error"}');

        $connectionContext->getConnection()->willReturn($connection);
        $connectionContext->isCollectable()->willReturn(true);
        $connection->flowType()->willReturn(new FlowType(FlowType::DATA_SOURCE));
        $connection->code()->willReturn($connectionCode);

        $extractErrorsFromHttpException->extractAll($exception, $connectionCode)->willReturn([
            $technicalError,
            $anotherTechError,
            $businessError,
            $anotherBusError,
        ]);

        $repository->bulkInsert([$businessError, $anotherBusError])->shouldBeCalled();

        $updateErrorCountHandler->handle(Argument::that(
            function (UpdateConnectionErrorCountCommand $command) use ($connectionCode) {
                $hourlyErrorCounts = $command->errorCounts();
                if (2 !== count($hourlyErrorCounts)) {
                    return false;
                }

                foreach ($hourlyErrorCounts as $hourlyErrorCount) {
                    if (!$hourlyErrorCount instanceof HourlyErrorCount ||
                        $connectionCode === $hourlyErrorCount->connectionCode() ||
                        2 !== $hourlyErrorCount->errorCount() ||
                        !in_array((string) $hourlyErrorCount->errorType(), ErrorTypes::getAll())
                    ) {
                        return false;
                    }
                }

                return true;
        }))->shouldBeCalled();

        $this->collectFromHttpException($exception);
        $this->flush();
    }

    public function it_doesnt_collect_errors_when_the_api_connection_is_not_found(
        $extractErrorsFromHttpException,
        $connectionContext,
        $repository,
        $updateErrorCountHandler
    ): void {
        $exception = new HttpException(400);
        $connectionContext->getConnection()->willReturn(null);

        $extractErrorsFromHttpException->extractAll($exception)->shouldNotBeCalled();
        $repository->bulkInsert(Argument::any())->shouldNotBeCalled();
        $updateErrorCountHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->collectFromHttpException($exception);
        $this->flush();
    }

    public function it_doesnt_collect_errors_when_the_api_connection_is_not_collectable(
        $extractErrorsFromHttpException,
        $connectionContext,
        Connection $connection,
        $repository,
        $updateErrorCountHandler
    ): void {
        $exception = new HttpException(400);
        $connectionContext->getConnection()->willReturn($connection);
        $connectionContext->isCollectable()->willReturn(false);

        $extractErrorsFromHttpException->extractAll($exception)->shouldNotBeCalled();
        $repository->bulkInsert(Argument::any())->shouldNotBeCalled();
        $updateErrorCountHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->collectFromHttpException($exception);
        $this->flush();
    }

    public function it_doesnt_collect_errors_when_the_api_connection_has_not_the_data_source_flow_type(
        $extractErrorsFromHttpException,
        $connectionContext,
        Connection $connection,
        $repository,
        $updateErrorCountHandler
    ): void {
        $exception = new HttpException(400);
        $connectionContext->getConnection()->willReturn($connection);
        $connectionContext->isCollectable()->willReturn(true);
        $connection->flowType()->willReturn(new FlowType(FlowType::OTHER));

        $extractErrorsFromHttpException->extractAll($exception)->shouldNotBeCalled();
        $repository->bulkInsert(Argument::any())->shouldNotBeCalled();
        $updateErrorCountHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->collectFromHttpException($exception);
        $this->flush();
    }
}
