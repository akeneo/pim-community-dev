<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\ErrorManagement\Service;

use Akeneo\Connectivity\Connection\Application\ErrorManagement\Command\UpdateConnectionErrorCountCommand;
use Akeneo\Connectivity\Connection\Application\ErrorManagement\Command\UpdateConnectionErrorCountHandler;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\ErrorTypes;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\BusinessError;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Persistence\Repository\BusinessErrorRepository;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Infrastructure\ConnectionContext;
use Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\ExtractErrorsFromHttpException;
use Akeneo\Tool\Component\Api\Exception\ViolationHttpException;
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
        $extractErrorsFromHttpException->extractAll($exception)->willReturn(['{"message":"My error!"}']);
        $connectionContext->getConnection()->willReturn($connection);
        $connectionContext->isCollectable()->willReturn(true);
        $connection->flowType()->willReturn(new FlowType(FlowType::DATA_SOURCE));
        $connection->code()->willReturn(new ConnectionCode('erp'));

        $repository->bulkInsert(Argument::containing(Argument::type(BusinessError::class)))
            ->shouldBeCalled();
        $updateErrorCountHandler->handle(Argument::that(function (UpdateConnectionErrorCountCommand $command) {
            return 'erp' === $command->connectionCode() &&
                1 === $command->errorCount() &&
                ErrorTypes::BUSINESS === $command->errorType();
        }))->shouldBeCalled();

        $this->collectFromHttpException($exception);
        $this->flush();
    }

    public function it_collects_several_errors_from_an_http_exception(
        $extractErrorsFromHttpException,
        $connectionContext,
        Connection $connection,
        $repository,
        $updateErrorCountHandler
    ): void {
        $exception = new HttpException(422);
        $extractErrorsFromHttpException
            ->extractAll($exception)
            ->willReturn(['{"message":"My error!"}', '{"message":"Another error!"}']);
        $connectionContext->getConnection()->willReturn($connection);
        $connectionContext->isCollectable()->willReturn(true);
        $connection->flowType()->willReturn(new FlowType(FlowType::DATA_SOURCE));
        $connection->code()->willReturn(new ConnectionCode('erp'));

        $repository
            ->bulkInsert(Argument::withEveryEntry(Argument::type(BusinessError::class)))
            ->shouldBeCalled();
        $updateErrorCountHandler->handle(Argument::that(function (UpdateConnectionErrorCountCommand $command) {
            return 'erp' === $command->connectionCode() &&
                2 === $command->errorCount() &&
                ErrorTypes::BUSINESS === $command->errorType();
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
        $extractErrorsFromHttpException->extractAll($exception)->willReturn(['{"message":"My error!"}']);
        $connectionContext->getConnection()->willReturn(null);

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
        $extractErrorsFromHttpException->extractAll($exception)->willReturn(['{"message":"My error!"}']);
        $connectionContext->getConnection()->willReturn($connection);
        $connectionContext->isCollectable()->willReturn(false);

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
        $extractErrorsFromHttpException->extractAll($exception)->willReturn(['{"message":"My error!"}']);
        $connectionContext->getConnection()->willReturn($connection);
        $connectionContext->isCollectable()->willReturn(true);
        $connection->flowType()->willReturn(new FlowType(FlowType::OTHER));

        $repository->bulkInsert(Argument::any())->shouldNotBeCalled();
        $updateErrorCountHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->collectFromHttpException($exception);
        $this->flush();
    }
}
