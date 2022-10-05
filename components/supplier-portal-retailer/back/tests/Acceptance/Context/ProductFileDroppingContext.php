<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Acceptance\Context;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\CommentProductFile;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\CommentProductFileHandler;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFileWithComments;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\CommentTooLong;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\EmptyComment;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\MaxCommentPerProductFileReached;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Repository\InMemory\InMemoryRepository as InMemoryProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository as InMemorySupplierRepository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\SupplierBuilder;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;

final class ProductFileDroppingContext implements Context
{
    private string $productFileIdentifier;
    private ?\Exception $exception = null;

    public function __construct(
        private InMemorySupplierRepository $supplierRepository,
        private InMemoryProductFileRepository $productFileRepository,
        private CommentProductFileHandler $commentProductFileHandler,
        private GetProductFileWithComments $getProductFileWithComments,
    ) {
    }

    /**
     * @Given a supplier
     */
    public function aSupplier(): void
    {
        $this->supplierRepository->save(
            (new SupplierBuilder())
                ->withIdentifier('f7555f61-2ea6-4b0e-88f2-737e504e7b95')
                ->build(),
        );
    }

    /**
     * @Given a product file
     */
    public function aProductFile(): void
    {
        $this->productFileIdentifier = '893e5eab-d85c-4c47-9c4f-3afc17d6b1eb';

        $this->productFileRepository->save(ProductFile::create(
            $this->productFileIdentifier,
            'file.xlsx',
            'path/to/file.xlsx',
            'jimmy.punchline@los-pollos-hermanos.com',
            new Read\Model\Supplier(
                'f7555f61-2ea6-4b0e-88f2-737e504e7b95',
                'supplier_code',
                'Supplier label',
            ),
        ));
    }

    /**
     * @Given a product file with 50 comments
     */
    public function aProductFileWithFifyComments(): void
    {
        $this->productFileIdentifier = '893e5eab-d85c-4c47-9c4f-3afc17d6b1eb';

        $productFile = ProductFile::create(
            $this->productFileIdentifier,
            'file.xlsx',
            'path/to/file.xlsx',
            'jimmy.punchline@los-pollos-hermanos.com',
            new Read\Model\Supplier(
                'f7555f61-2ea6-4b0e-88f2-737e504e7b95',
                'supplier_code',
                'Supplier label',
            ),
        );

        for ($i = 0; 50 > $i; $i++) {
            $productFile->addNewRetailerComment('foo', 'julia@roberts.com', new \DateTimeImmutable());
        }

        $this->productFileRepository->save($productFile);
    }

    /**
     * @When I comment it with :content
     */
    public function iCommentItWith(string $content): void
    {
        try {
            ($this->commentProductFileHandler)(new CommentProductFile(
                $this->productFileIdentifier,
                'julia@roberts.com',
                $content,
                new \DateTimeImmutable(),
            ));
        } catch (\Exception $e) {
            $this->exception = $e;
        }
    }

    /**
     * @When I comment it with a too long comment
     */
    public function iCommentItWithATooLongComment(): void
    {
        try {
            ($this->commentProductFileHandler)(new CommentProductFile(
                $this->productFileIdentifier,
                'julia@roberts.com',
                str_repeat('q', 256),
                new \DateTimeImmutable(),
            ));
        } catch (\Exception $e) {
            $this->exception = $e;
        }
    }

    /**
     * @Then I should have an error message telling that the comment should not be empty
     */
    public function iShouldHaveAnErrorForEmptyComment(): void
    {
        Assert::assertInstanceOf(EmptyComment::class, $this->exception);
    }

    /**
     * @Then I should have an error message telling that the comment should not exceed 255 characters
     */
    public function iShouldHaveAnErrorForTooLongComment(): void
    {
        Assert::assertInstanceOf(CommentTooLong::class, $this->exception);
    }

    /**
     * @Then I should have an error message telling that the product file cannot have more than 50 comments
     */
    public function iShouldHaveAnErrorForTooManyCommentsOnProductFile(): void
    {
        Assert::assertInstanceOf(MaxCommentPerProductFileReached::class, $this->exception);
    }

    /**
     * @Then the product file contains the comment :content
     */
    public function theProductFileContainsTheComment(string $content): void
    {
        $productFile = ($this->getProductFileWithComments)($this->productFileIdentifier);

        Assert::assertCount(1, $productFile->retailerComments);
        Assert::assertSame($content, $productFile->retailerComments[0]->content());
    }
}
