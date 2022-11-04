<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Infrastructure\ProductFileDropping\ServiceAPI\UploadProductFile;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Exception\InvalidProductFile;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\CreateProductFile\CreateProductFile;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\CreateProductFile\CreateProductFileHandler;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\ContributorDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\UnableToStoreProductFile;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\UploadProductFile\Exception\InvalidUploadedProductFile;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\UploadProductFile\Exception\UnableToStoreProductFile as UnableToStoreProductFileServiceAPI;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\UploadProductFile\UploadProductFile;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\UploadProductFile\UploadProductFileCommand;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\ConstraintViolationList;

final class UploadProductFileTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        touch('/tmp/product_file.xlsx');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unlink('/tmp/product_file.xlsx');
    }

    /** @test */
    public function itUploadsAProductFile(): void
    {
        $createProductFileHandler = $this->createMock(CreateProductFileHandler::class);
        $logger = new TestLogger();
        $sut = new UploadProductFile($createProductFileHandler, $logger);

        $createProductFileHandler
            ->expects($this->once())
            ->method('__invoke')
            ->with(new CreateProductFile(
                'product_file.xlsx',
                '/tmp/product_file.xlsx',
                'jimmy@supplier.com',
            ))
        ;

        ($sut)(new UploadProductFileCommand(
            new UploadedFile('/tmp/product_file.xlsx', 'product_file.xlsx'),
            'jimmy@supplier.com',
        ));
    }

    /** @test */
    public function itReturnsAServiceAPIExceptionIfAnApplicationInvalidProductFileExceptionOccurred(): void
    {
        $createProductFileHandler = $this->createMock(CreateProductFileHandler::class);
        $logger = new TestLogger();
        $sut = new UploadProductFile($createProductFileHandler, $logger);

        $createProductFileHandler
            ->expects($this->once())
            ->method('__invoke')
            ->with(new CreateProductFile(
                'product_file.xlsx',
                '/tmp/product_file.xlsx',
                'jimmy@supplier.com',
            ))
            ->willThrowException(new InvalidProductFile(new ConstraintViolationList()))
        ;

        touch('/tmp/product_file.xlsx');
        try {
            ($sut)(new UploadProductFileCommand(
                new UploadedFile('/tmp/product_file.xlsx', 'product_file.xlsx'),
                'jimmy@supplier.com',
            ));
        } catch (InvalidUploadedProductFile $e) {
            self::assertTrue(true);

            return;
        }

        self::fail(sprintf('Expected a "%s" exception.', InvalidUploadedProductFile::class));
    }

    /** @test */
    public function itReturnsAServiceAPIExceptionIfADomainContributorDoesNotExistExceptionOccurred(): void
    {
        $createProductFileHandler = $this->createMock(CreateProductFileHandler::class);
        $logger = new TestLogger();
        $sut = new UploadProductFile($createProductFileHandler, $logger);

        $createProductFileHandler
            ->expects($this->once())
            ->method('__invoke')
            ->with(new CreateProductFile(
                'product_file.xlsx',
                '/tmp/product_file.xlsx',
                'jimmy@supplier.com',
            ))
            ->willThrowException(new ContributorDoesNotExist())
        ;

        try {
            ($sut)(new UploadProductFileCommand(
                new UploadedFile('/tmp/product_file.xlsx', 'product_file.xlsx'),
                'jimmy@supplier.com',
            ));
        } catch (InvalidUploadedProductFile) {
            static::assertTrue($logger->hasError([
                'message' => 'The product file upload failed because the contributor does not exist.',
                'context' => [
                    'data' => [
                        'contributor_email' => 'jimmy@supplier.com',
                    ],
                ],
            ]));

            return;
        }

        self::fail(sprintf('Expected a "%s" exception.', InvalidUploadedProductFile::class));
    }

    /** @test */
    public function itReturnsAServiceAPIExceptionIfADomainUnableToStoreProductFileExceptionOccurred(): void
    {
        $createProductFileHandler = $this->createMock(CreateProductFileHandler::class);
        $logger = new TestLogger();
        $sut = new UploadProductFile($createProductFileHandler, $logger);

        $createProductFileHandler
            ->expects($this->once())
            ->method('__invoke')
            ->with(new CreateProductFile(
                'product_file.xlsx',
                '/tmp/product_file.xlsx',
                'jimmy@supplier.com',
            ))
            ->willThrowException(new UnableToStoreProductFile())
        ;

        try {
            ($sut)(new UploadProductFileCommand(
                new UploadedFile('/tmp/product_file.xlsx', 'product_file.xlsx'),
                'jimmy@supplier.com',
            ));
        } catch (UnableToStoreProductFileServiceAPI) {
            static::assertTrue(true);

            return;
        }

        self::fail(sprintf('Expected a "%s" exception.', UnableToStoreProductFile::class));
    }
}
