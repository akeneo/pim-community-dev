<?php

declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Application\ErrorManagement\Service;

use Akeneo\Connectivity\Connection\Application\ConnectionContextInterface;
use Akeneo\Connectivity\Connection\Application\ErrorManagement\Command\UpdateConnectionErrorCountCommand;
use Akeneo\Connectivity\Connection\Application\ErrorManagement\Command\UpdateConnectionErrorCountHandler;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\ErrorTypes;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\BusinessError;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\HourlyErrorCount;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Persistence\Repository\BusinessErrorRepositoryInterface;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\ConnectionCode;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\ValueObject\FlowType;
use Akeneo\Connectivity\Connection\Domain\Settings\Model\Write\Connection;
use Akeneo\Pim\Enrichment\Component\Error\DomainErrorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Serializer\Serializer;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;

class CollectApiErrorSpec extends ObjectBehavior
{
    public function let(
        ConnectionContextInterface $connectionContext,
        BusinessErrorRepositoryInterface $repository,
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

    public function it_collects_a_business_error_from_a_product_domain_error(
        $connectionContext,
        $repository,
        $updateErrorCountHandler,
        $serializer,
        Connection $connection,
        ProductInterface $product,
        DomainErrorInterface $error
    ): void {
        $connectionContext->getConnection()->willReturn($connection);
        $connectionContext->isCollectable()->willReturn(true);

        $context = (new Context())->setAttribute('product', $product);

        $connection->code()->willReturn(new ConnectionCode('erp'));
        $connection->flowType()->willReturn(new FlowType(FlowType::DATA_SOURCE));

        $serializer->serialize($error, 'json', Argument::any())
            ->willReturn('{"message":"business error"}');

        $updateErrorCountHandler->handle(Argument::that(function (UpdateConnectionErrorCountCommand $command): bool {
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

        $repository->bulkInsert(new ConnectionCode('erp'), Argument::that(function (array $businessErrors): bool {
            Assert::assertCount(1, $businessErrors);

            Assert::assertInstanceOf(BusinessError::class, $businessErrors[0]);
            Assert::assertSame('{"message":"business error"}', $businessErrors[0]->content());

            return true;
        }))
            ->shouldBeCalled();

        $this->collectFromProductDomainError($error, $context);
        $this->flush();
    }

    public function it_collects_business_errors_from_a_product_validation_error(
        $connectionContext,
        $repository,
        $updateErrorCountHandler,
        $serializer,
        Connection $connection,
        ProductInterface $product,
        ConstraintViolationInterface $violation1,
        ConstraintViolationInterface $violation2
    ): void {
        $violationList = new ConstraintViolationList([
            $violation1->getWrappedObject(),
            $violation2->getWrappedObject()
        ]);

        $context = (new Context())->setAttribute('product', $product);

        $connectionContext->getConnection()->willReturn($connection);
        $connectionContext->isCollectable()->willReturn(true);

        $connection->code()->willReturn(new ConnectionCode('erp'));
        $connection->flowType()->willReturn(new FlowType(FlowType::DATA_SOURCE));

        $serializer->serialize($violation1, 'json', Argument::any())
            ->willReturn('{"message":"business error 1"}');

        $serializer->serialize($violation2, 'json', Argument::any())
            ->willReturn('{"message":"business error 2"}');

        $updateErrorCountHandler->handle(Argument::that(function (UpdateConnectionErrorCountCommand $command): bool {
            $hourlyErrorCounts = $command->errorCounts();
            Assert::assertCount(2, $hourlyErrorCounts);

            Assert::assertInstanceOf(HourlyErrorCount::class, $hourlyErrorCounts[0]);
            Assert::assertSame('erp', (string) $hourlyErrorCounts[0]->connectionCode());
            Assert::assertEquals(ErrorTypes::BUSINESS, (string) $hourlyErrorCounts[0]->errorType());
            Assert::assertSame(2, $hourlyErrorCounts[0]->errorCount());

            Assert::assertInstanceOf(HourlyErrorCount::class, $hourlyErrorCounts[1]);
            Assert::assertSame('erp', (string) $hourlyErrorCounts[1]->connectionCode());
            Assert::assertEquals(ErrorTypes::TECHNICAL, $hourlyErrorCounts[1]->errorType());
            Assert::assertSame(0, $hourlyErrorCounts[1]->errorCount());

            return true;
        }))
            ->shouldBeCalled();

        $repository->bulkInsert(new ConnectionCode('erp'), Argument::that(function (array $businessErrors): bool {
            Assert::assertCount(2, $businessErrors);

            Assert::assertInstanceOf(BusinessError::class, $businessErrors[0]);
            Assert::assertSame('{"message":"business error 1"}', $businessErrors[0]->content());

            Assert::assertInstanceOf(BusinessError::class, $businessErrors[1]);
            Assert::assertSame('{"message":"business error 2"}', $businessErrors[1]->content());

            return true;
        }))
            ->shouldBeCalled();

        $this->collectFromProductValidationError($violationList, $context);
        $this->flush();
    }

    public function it_collects_a_technical_error(
        $connectionContext,
        $repository,
        $updateErrorCountHandler,
        Connection $connection
    ): void {
        $connectionContext->getConnection()->willReturn($connection);
        $connectionContext->isCollectable()->willReturn(true);

        $connection->code()->willReturn(new ConnectionCode('erp'));
        $connection->flowType()->willReturn(new FlowType(FlowType::DATA_SOURCE));

        $updateErrorCountHandler->handle(Argument::that(function (UpdateConnectionErrorCountCommand $command): bool {
            $hourlyErrorCounts = $command->errorCounts();
            Assert::assertCount(2, $hourlyErrorCounts);

            Assert::assertInstanceOf(HourlyErrorCount::class, $hourlyErrorCounts[0]);
            Assert::assertSame('erp', (string) $hourlyErrorCounts[0]->connectionCode());
            Assert::assertEquals(ErrorTypes::BUSINESS, (string) $hourlyErrorCounts[0]->errorType());
            Assert::assertSame(0, $hourlyErrorCounts[0]->errorCount());

            Assert::assertInstanceOf(HourlyErrorCount::class, $hourlyErrorCounts[1]);
            Assert::assertSame('erp', (string) $hourlyErrorCounts[1]->connectionCode());
            Assert::assertEquals(ErrorTypes::TECHNICAL, $hourlyErrorCounts[1]->errorType());
            Assert::assertSame(1, $hourlyErrorCounts[1]->errorCount());

            return true;
        }))
            ->shouldBeCalled();

        $repository->bulkInsert(new ConnectionCode('erp'), Argument::that(function (array $businessErrors): bool {
            Assert::assertCount(0, $businessErrors);

            return true;
        }))
            ->shouldBeCalled();

        $this->collectFromTechnicalError(new \Exception());
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
