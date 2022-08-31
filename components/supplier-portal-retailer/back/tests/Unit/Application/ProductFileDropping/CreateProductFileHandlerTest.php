<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Application\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\CreateProductFile;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\CreateProductFileHandler;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Exception\InvalidProductFile;
use Akeneo\SupplierPortal\Retailer\Application\Supplier\Exception\ContributorDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\StoreProductsFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Event\SupplierFileAdded;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\SupplierFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\ContributorEmail;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Filename;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\ValueObject\Code;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Repository\InMemory\InMemoryRepository as SupplierFileInMemoryRepository;
use Akeneo\SupplierPortal\Retailer\Infrastructure\StubEventDispatcher;
use Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Query\InMemory\InMemoryGetSupplierFromContributorEmail;
use Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository as SupplierInMemoryRepository;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Psr\Log\Test\TestLogger;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactoryInterface;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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

        $supplier = Supplier::create(
            '01319d4c-81c4-4f60-a992-41ea3546824c',
            'mysupplier',
            'My Supplier',
            ['contributor@example.com'],
        );
        $supplierRepository = new SupplierInMemoryRepository();
        $supplierRepository->save($supplier);

        $supplierFileRepository = new SupplierFileInMemoryRepository();
        $getSupplierFromContributorEmail = new InMemoryGetSupplierFromContributorEmail($supplierRepository);
        $storeProductFileSpy = $this->createMock(StoreProductsFile::class);
        $eventDispatcherStub = new StubEventDispatcher();

        $uploadedSupplierFile = $this->createMock(UploadedFile::class);
        $createSupplierFile = new CreateProductFile(
            $uploadedSupplierFile,
            'contributor@example.com',
        );
        $uploadedSupplierFile->expects($this->once())->method('getPathname')->willReturn('/tmp/products.xlsx');
        $uploadedSupplierFile->expects($this->exactly(4))->method('getClientOriginalName')->willReturn('products.xlsx');

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
        $factory
            ->method('uuid4')
            ->willReturn($uuidInterface)
        ;
        $this->factory = Uuid::getFactory();
        Uuid::setFactory($factory);

        $logger = new TestLogger();

        $sut = new CreateProductFileHandler(
            $getSupplierFromContributorEmail,
            $supplierFileRepository,
            $storeProductFileSpy,
            $validatorSpy,
            $eventDispatcherStub,
            $logger,
        );
        ($sut)($createSupplierFile);

        $file = $supplierFileRepository->findByContributor(ContributorEmail::fromString('contributor@example.com'));

        static::assertInstanceOf(SupplierFile::class, $file);

        $dispatchedEvents = $eventDispatcherStub->getDispatchedEvents();
        $this->assertCount(1, $dispatchedEvents);
        $this->assertInstanceOf(SupplierFileAdded::class, $dispatchedEvents[0]);

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
    public function itThrowsAnExceptionIfTheSupplierFileIsNotValid(): void
    {
        $violationsSpy = $this->createMock(ConstraintViolationList::class);
        $violationsSpy->expects($this->once())->method('count')->willReturn(1);

        $validatorSpy = $this->createMock(ValidatorInterface::class);
        $validatorSpy
            ->expects($this->once())
            ->method('validate')
            ->willReturn($violationsSpy);

        $uploadedSupplierFile = $this->createMock(UploadedFile::class);

        $supplierRepository = new SupplierInMemoryRepository();
        $getSupplierFromContributorEmail = new InMemoryGetSupplierFromContributorEmail($supplierRepository);
        $supplierFileRepository = new SupplierFileInMemoryRepository();
        $storeProductFileSpy = $this->createMock(StoreProductsFile::class);
        $eventDispatcherStub = new StubEventDispatcher();

        $sut = new CreateProductFileHandler(
            $getSupplierFromContributorEmail,
            $supplierFileRepository,
            $storeProductFileSpy,
            $validatorSpy,
            $eventDispatcherStub,
            new NullLogger(),
        );

        static::expectException(InvalidProductFile::class);
        ($sut)(
            new CreateProductFile(
                $uploadedSupplierFile,
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

        $uploadedSupplierFile = $this->createMock(UploadedFile::class);

        $supplierRepository = new SupplierInMemoryRepository();
        $getSupplierFromContributorEmail = new InMemoryGetSupplierFromContributorEmail($supplierRepository);
        $supplierFileRepository = new SupplierFileInMemoryRepository();
        $storeProductFileSpy = $this->createMock(StoreProductsFile::class);
        $eventDispatcherStub = new StubEventDispatcher();

        $sut = new CreateProductFileHandler(
            $getSupplierFromContributorEmail,
            $supplierFileRepository,
            $storeProductFileSpy,
            $validatorSpy,
            $eventDispatcherStub,
            new NullLogger(),
        );

        static::expectException(ContributorDoesNotExist::class);
        ($sut)(
            new CreateProductFile(
                $uploadedSupplierFile,
                'contributor@example.com',
            )
        );
    }
}
