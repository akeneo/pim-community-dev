<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Application\ProductFileDropping\Write\CreateProductFile;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Exception\InvalidProductFile;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\CreateProductFile\CreateProductFile;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\CreateProductFile\CreateProductFileHandler;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\StoreProductsFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Event\ProductFileAdded;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\ContributorDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile\ContributorEmail;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile\Filename;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile\Identifier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier\Code;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Repository\InMemory\InMemoryRepository as ProductFileInMemoryRepository;
use Akeneo\SupplierPortal\Retailer\Infrastructure\StubEventDispatcher;
use Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Query\InMemory\InMemoryGetSupplierFromContributorEmail;
use Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository as SupplierInMemoryRepository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Psr\Log\Test\TestLogger;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactoryInterface;
use Ramsey\Uuid\UuidInterface;
use Ramsey\Uuid\Validator\GenericValidator;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CreateProductFileHandlerTest extends TestCase
{
    private ?UuidFactoryInterface $factory = null;

    protected function tearDown(): void
    {
        if (null !== $this->factory) {
            Uuid::setFactory($this->factory);
        }
    }

    /** @test */
    public function itCreatesAProductFile(): void
    {
        $violationsSpy = $this->createMock(ConstraintViolationList::class);
        $violationsSpy->expects($this->once())->method('count')->willReturn(0);

        $validatorSpy = $this->createMock(ValidatorInterface::class);
        $validatorSpy
            ->expects($this->once())
            ->method('validate')
            ->willReturn($violationsSpy);

        $supplier = (new SupplierBuilder())
            ->withIdentifier('01319d4c-81c4-4f60-a992-41ea3546824c')
            ->withCode('mysupplier')
            ->withLabel('My Supplier')
            ->withContributors(['contributor@example.com'])
            ->build();

        $supplierRepository = new SupplierInMemoryRepository();
        $supplierRepository->save($supplier);

        $productFileRepository = new ProductFileInMemoryRepository();
        $getSupplierFromContributorEmail = new InMemoryGetSupplierFromContributorEmail($supplierRepository);
        $storeProductFileSpy = $this->createMock(StoreProductsFile::class);
        $eventDispatcherStub = new StubEventDispatcher();

        $createProductFile = new CreateProductFile(
            'products.xlsx',
            '/tmp/products.xlsx',
            'contributor@example.com',
        );

        $storeProductFileSpy
            ->expects($this->once())
            ->method('__invoke')
            ->with(
                Code::fromString($supplier->code()),
                Filename::fromString('products.xlsx'),
                $this->isInstanceOf(Identifier::class),
                '/tmp/products.xlsx',
            )->willReturn('a_path');

        $uuidInterface = $this->createMock(UuidInterface::class);
        $uuidInterface->method('toString')->willReturn('e36f227c-2946-11e8-b467-0ed5f89f718b');
        $factory = $this->createMock(UuidFactoryInterface::class);
        $factory->method('uuid4')->willReturn($uuidInterface);
        $factory->method('getValidator')->willReturn(new GenericValidator());

        $this->factory = Uuid::getFactory();
        Uuid::setFactory($factory);

        $logger = new TestLogger();

        $sut = new CreateProductFileHandler(
            $getSupplierFromContributorEmail,
            $productFileRepository,
            $storeProductFileSpy,
            $validatorSpy,
            $eventDispatcherStub,
            $logger,
        );
        ($sut)($createProductFile);

        $file = $productFileRepository->findByContributor(ContributorEmail::fromString('contributor@example.com'));

        static::assertInstanceOf(ProductFile::class, $file);

        $dispatchedEvents = $eventDispatcherStub->getDispatchedEvents();
        $this->assertCount(1, $dispatchedEvents);
        $this->assertInstanceOf(ProductFileAdded::class, $dispatchedEvents[0]);

        static::assertTrue($logger->hasInfo([
            'message' => 'Product file "products.xlsx" created.',
            'context' => [
                'data' => [
                    'identifier' => 'e36f227c-2946-11e8-b467-0ed5f89f718b',
                    'supplier_identifier' => '01319d4c-81c4-4f60-a992-41ea3546824c',
                    'supplier_code' => 'mysupplier',
                    'filename' => 'products.xlsx',
                    'path' => 'a_path',
                    'uploaded_by_contributor' => 'contributor@example.com',
                    'metric_key' => 'supplier_file_dropped',
                ],
            ],
        ]));
    }

    /** @test */
    public function itThrowsAnExceptionIfTheProductFileIsNotValid(): void
    {
        $violationsSpy = $this->createMock(ConstraintViolationList::class);
        $violationsSpy->expects($this->once())->method('count')->willReturn(1);

        $validatorSpy = $this->createMock(ValidatorInterface::class);
        $validatorSpy
            ->expects($this->once())
            ->method('validate')
            ->willReturn($violationsSpy);

        $supplierRepository = new SupplierInMemoryRepository();
        $getSupplierFromContributorEmail = new InMemoryGetSupplierFromContributorEmail($supplierRepository);
        $productFileRepository = new ProductFileInMemoryRepository();
        $storeProductFileSpy = $this->createMock(StoreProductsFile::class);
        $eventDispatcherStub = new StubEventDispatcher();

        $sut = new CreateProductFileHandler(
            $getSupplierFromContributorEmail,
            $productFileRepository,
            $storeProductFileSpy,
            $validatorSpy,
            $eventDispatcherStub,
            new NullLogger(),
        );

        static::expectException(InvalidProductFile::class);
        ($sut)(
            new CreateProductFile(
                'products.xlsx',
                '/tmp/products.xlsx',
                'contributor@example.com',
            )
        );
    }

    /** @test */
    public function itThrowsAnExceptionIfTheContributorDoesNotExist(): void
    {
        $violationsSpy = $this->createMock(ConstraintViolationList::class);
        $violationsSpy->expects($this->once())->method('count')->willReturn(0);

        $validatorSpy = $this->createMock(ValidatorInterface::class);
        $validatorSpy
            ->expects($this->once())
            ->method('validate')
            ->willReturn($violationsSpy);

        $supplierRepository = new SupplierInMemoryRepository();
        $getSupplierFromContributorEmail = new InMemoryGetSupplierFromContributorEmail($supplierRepository);
        $productFileRepository = new ProductFileInMemoryRepository();
        $storeProductFileSpy = $this->createMock(StoreProductsFile::class);
        $eventDispatcherStub = new StubEventDispatcher();

        $sut = new CreateProductFileHandler(
            $getSupplierFromContributorEmail,
            $productFileRepository,
            $storeProductFileSpy,
            $validatorSpy,
            $eventDispatcherStub,
            new NullLogger(),
        );

        static::expectException(ContributorDoesNotExist::class);
        ($sut)(
            new CreateProductFile(
                'products.xlsx',
                '/tmp/products.xlsx',
                'contributor@example.com',
            )
        );
    }
}
