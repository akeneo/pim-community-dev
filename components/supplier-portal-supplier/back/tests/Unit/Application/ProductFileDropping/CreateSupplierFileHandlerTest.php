<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Application\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Application\Supplier\Exception\ContributorDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\ValueObject\Code;
use Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Query\InMemory\InMemoryGetSupplierFromContributorEmail;
use Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository as SupplierInMemoryRepository;
use Akeneo\SupplierPortal\Supplier\Application\ProductFileDropping\CreateSupplierFile;
use Akeneo\SupplierPortal\Supplier\Application\ProductFileDropping\CreateSupplierFileHandler;
use Akeneo\SupplierPortal\Supplier\Application\ProductFileDropping\Exception\InvalidSupplierFile;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\StoreProductsFile;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\Event\SupplierFileAdded;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\Model\SupplierFile;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\ContributorEmail;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Filename;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Supplier\Infrastructure\ProductFileDropping\Repository\InMemory\InMemoryRepository as SupplierFileInMemoryRepository;
use Akeneo\SupplierPortal\Supplier\Infrastructure\StubEventDispatcher;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CreateSupplierFileHandlerTest extends TestCase
{
    /** @test */
    public function itCreatesASupplierFile(): void
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
        $createSupplierFile = new CreateSupplierFile(
            $uploadedSupplierFile,
            'products.xlsx',
            'contributor@example.com',
        );
        $uploadedSupplierFile->expects($this->once())->method('getPathname')->willReturn('/tmp/products.xlsx');

        $storeProductFileSpy
            ->expects($this->once())
            ->method('__invoke')
            ->with(
                Code::fromString($supplier->code()),
                Filename::fromString($createSupplierFile->originalFilename),
                $this->isInstanceOf(Identifier::class),
                '/tmp/products.xlsx',
            )->willReturn('a_path');

        $sut = new CreateSupplierFileHandler(
            $getSupplierFromContributorEmail,
            $supplierFileRepository,
            $storeProductFileSpy,
            $validatorSpy,
            $eventDispatcherStub,
            new NullLogger(),
        );
        ($sut)($createSupplierFile);

        $file = $supplierFileRepository->findByContributor(ContributorEmail::fromString('contributor@example.com'));

        static::assertInstanceOf(SupplierFile::class, $file);

        $dispatchedEvents = $eventDispatcherStub->getDispatchedEvents();
        $this->assertCount(1, $dispatchedEvents);
        $this->assertInstanceOf(SupplierFileAdded::class, $dispatchedEvents[0]);
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

        $sut = new CreateSupplierFileHandler(
            $getSupplierFromContributorEmail,
            $supplierFileRepository,
            $storeProductFileSpy,
            $validatorSpy,
            $eventDispatcherStub,
            new NullLogger(),
        );

        static::expectException(InvalidSupplierFile::class);
        ($sut)(
            new CreateSupplierFile(
                $uploadedSupplierFile,
                'products.xlsx',
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

        $sut = new CreateSupplierFileHandler(
            $getSupplierFromContributorEmail,
            $supplierFileRepository,
            $storeProductFileSpy,
            $validatorSpy,
            $eventDispatcherStub,
            new NullLogger(),
        );

        static::expectException(ContributorDoesNotExist::class);
        ($sut)(
            new CreateSupplierFile(
                $uploadedSupplierFile,
                'products.xlsx',
                'contributor@example.com',
            )
        );
    }
}
