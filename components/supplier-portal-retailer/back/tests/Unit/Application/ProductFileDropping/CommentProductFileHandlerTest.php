<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Application\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\CommentProductFile;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\CommentProductFileHandler;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Repository\InMemory\InMemoryRepository as ProductFileInMemoryRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class CommentProductFileHandlerTest extends TestCase
{
    /** @test */
    public function itCommentsAProductFile(): void
    {
        $productFileRepository = new ProductFileInMemoryRepository();
        $productFileRepository->save(ProductFile::create(
            '6ffc16ae-3e0d-4a10-a8c3-7e33e2a4c287',
            'file.xlsx',
            'path/to/file.xlsx',
            'julia@roberts.com',
            new Supplier(
                '64e9aa37-5935-4092-bbe6-54fe271fb2a7',
                'los_pollos_hermanos',
                'Los Pollos Hermanos',
            ),
        ));
        $command = new CommentProductFile(
            '6ffc16ae-3e0d-4a10-a8c3-7e33e2a4c287',
            'julia@roberts.com',
            'Your product file is awesome!',
            new \DateTimeImmutable(),
        );
        $sut = new CommentProductFileHandler($productFileRepository, $this->getValidatorSpyWithNoError($command));

        ($sut)($command);

        $productFile = $productFileRepository->find(
            Identifier::fromString(
                '6ffc16ae-3e0d-4a10-a8c3-7e33e2a4c287',
            ),
        );

        static::assertInstanceOf(ProductFile::class, $productFile);
        static::assertCount(1, $productFile->newRetailerComments());
        static::assertSame('julia@roberts.com', $productFile->newRetailerComments()[0]->authorEmail());
        static::assertSame('Your product file is awesome!', $productFile->newRetailerComments()[0]->content());
    }

    /** @test */
    public function itThrowsAnExceptionIfWeTryToCommentAProductFileThatDoesNotExist(): void
    {
        static::expectExceptionObject(new ProductFileDoesNotExist());

        $productFileRepository = new ProductFileInMemoryRepository();
        $command = new CommentProductFile(
            '6ffc16ae-3e0d-4a10-a8c3-7e33e2a4c287',
            'julia@roberts.com',
            'Your product file is awesome!',
            new \DateTimeImmutable(),
        );
        $sut = new CommentProductFileHandler($productFileRepository, $this->getValidatorSpyWithNoError($command));

        ($sut)($command);
    }

    private function getValidatorSpyWithNoError(CommentProductFile $command): ValidatorInterface
    {
        $violationsSpy = $this->createMock(ConstraintViolationList::class);
        $violationsSpy->expects($this->once())->method('count')->willReturn(0);

        $validatorSpy = $this->createMock(ValidatorInterface::class);
        $validatorSpy->expects($this->once())->method('validate')->with($command)->willReturn($violationsSpy);

        return $validatorSpy;
    }
}
