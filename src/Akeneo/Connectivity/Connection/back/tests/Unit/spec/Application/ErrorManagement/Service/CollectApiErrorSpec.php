<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\ErrorManagement\Service;

use Akeneo\Connectivity\Connection\Application\ConnectionContextInterface;
use Akeneo\Connectivity\Connection\Application\ErrorManagement\Command\UpdateConnectionErrorCountCommand;
use Akeneo\Connectivity\Connection\Application\ErrorManagement\Command\UpdateConnectionErrorCountHandler;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\ErrorTypes;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\ValueObject\ErrorType;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\BusinessError;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\HourlyErrorCount;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\TechnicalError;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Persistence\Repository\BusinessErrorRepository;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Connectivity\Connection\Infrastructure\ErrorManagement\ExtractErrorsFromHttpException;
use Akeneo\Pim\Enrichment\Component\Error\IdentifiableDomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use FOS\RestBundle\Serializer\Serializer;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;
use Prophecy\Argument;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CollectApiErrorSpec extends ObjectBehavior
{
    public function let(
        ConnectionContextInterface $connectionContext,
        BusinessErrorRepository $repository,
        UpdateConnectionErrorCountHandler $updateErrorCountHandler,
        Serializer $serializer
    ): void {
        $this->beConstructedWith(
            $connectionContext,
            $repository,
            $updateErrorCountHandler,
            $serializer
        );
    }

    public function it_collects_an_error_from_a_product_domain_error(
        $connectionContext,
        $repository,
        $updateErrorCountHandler,
        $serializer,
        Connection $connection,
        ProductInterface $product,
        IdentifiableDomainErrorInterface $error
    ): void {
        $connectionContext->getConnection()->willReturn($connection);
        $connectionContext->isCollectable()->willReturn(true);

        $connection->code()->willReturn(new ConnectionCode('erp'));
        $connection->flowType()->willReturn(new FlowType(FlowType::DATA_SOURCE));

        $serializer->serialize($error, 'json', Argument::any())
            ->willReturn('{"message":"business error"}');

        $updateErrorCountHandler->handle(Argument::that(function (UpdateConnectionErrorCountCommand $command) {
            $hourlyErrorCounts = $command->errorCounts();
            Assert::assertCount(2, $hourlyErrorCounts);

            Assert::assertInstanceOf(HourlyErrorCount::class, $hourlyErrorCounts[0]);
            Assert::assertSame('erp', (string) $hourlyErrorCounts[0]->connectionCode());
            Assert::assertEquals(ErrorTypes::BUSINESS, (string) $hourlyErrorCounts[0]->errorType());
            Assert::assertSame(1, $hourlyErrorCounts[0]->errorCount());

            Assert::assertInstanceOf(HourlyErrorCount::class, $hourlyErrorCounts[1]);
            Assert::assertSame('erp', (string) $hourlyErrorCounts[1]->connectionCode());
            Assert::assertEquals(ErrorTypes::TECHNICAL, $hourlyErrorCounts[1]->errorType());
            Assert::assertSame(0, $hourlyErrorCounts[1]->errorCount());

            return true;
        }))
            ->shouldBeCalled();

        $repository->bulkInsert(new ConnectionCode('erp'), Argument::that(function (array $businessErrors) {
            Assert::assertCount(1, $businessErrors);

            Assert::assertInstanceOf(BusinessError::class, $businessErrors[0]);
            Assert::assertSame('{"message":"business error"}', $businessErrors[0]->content());

            return true;
        }))
            ->shouldBeCalled();

        $this->collectFromProductDomainError($product, $error);
        $this->flush();
    }

    public function it_doesnt_collect_errors_when_the_api_connection_is_not_found(
        $connectionContext,
        $repository,
        $updateErrorCountHandler
    ): void {
        $connectionContext->getConnection()->willReturn(null);

        $repository->bulkInsert(Argument::any())->shouldNotBeCalled();
        $updateErrorCountHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->collectFromTechnicalError(new \Exception());
        $this->flush();
    }

    public function it_doesnt_collect_errors_when_the_api_connection_is_not_collectable(
        $connectionContext,
        Connection $connection,
        $repository,
        $updateErrorCountHandler
    ): void {
        $connectionContext->getConnection()->willReturn($connection);
        $connectionContext->isCollectable()->willReturn(false);

        $repository->bulkInsert(Argument::any())->shouldNotBeCalled();
        $updateErrorCountHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->collectFromTechnicalError(new \Exception());
        $this->flush();
    }

    public function it_doesnt_collect_errors_when_the_api_connection_has_not_the_data_source_flow_type(
        $connectionContext,
        Connection $connection,
        $repository,
        $updateErrorCountHandler
    ): void {
        $connectionContext->getConnection()->willReturn($connection);
        $connectionContext->isCollectable()->willReturn(true);
        $connection->flowType()->willReturn(new FlowType(FlowType::OTHER));

        $repository->bulkInsert(Argument::any())->shouldNotBeCalled();
        $updateErrorCountHandler->handle(Argument::any())->shouldNotBeCalled();

        $this->collectFromTechnicalError(new \Exception());
        $this->flush();
    }
}
