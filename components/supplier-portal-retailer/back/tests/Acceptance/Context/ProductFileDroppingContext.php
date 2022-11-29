<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Acceptance\Context;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\CommentProductFile\CommentProductFile;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\CommentProductFile\CommentProductFileHandler;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\CommentProductFileForSupplier\CommentProductFileForSupplier;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\CommentProductFileForSupplier\CommentProductFileHandlerForSupplier;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\MarkCommentsAsReadByRetailer\MarkCommentsAsReadByRetailer;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\MarkCommentsAsReadByRetailer\MarkCommentsAsReadByRetailerHandler;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\MarkCommentsAsReadBySupplier\MarkCommentsAsReadBySupplier;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\MarkCommentsAsReadBySupplier\MarkCommentsAsReadBySupplierHandler;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFileWithMetadataAndComments;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\CommentTooLong;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\EmptyComment;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\MaxCommentPerProductFileReached;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile\Identifier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\Supplier;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Repository\InMemory\InMemoryRepository as InMemoryProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository as InMemorySupplierRepository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder;
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
        private GetProductFileWithMetadataAndComments $getProductFileWithMedataAndComments,
        private CommentProductFileHandlerForSupplier $commentProductFileHandlerForSupplier,
        private MarkCommentsAsReadBySupplierHandler $markCommentsAsReadBySupplierHandler,
        private MarkCommentsAsReadByRetailerHandler $markCommentsAsReadByRetailerHandler,
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

        $this->productFileRepository->save(
            (new ProductFileBuilder())
                ->withIdentifier($this->productFileIdentifier)
                ->uploadedBySupplier(
                    new Supplier(
                        'f7555f61-2ea6-4b0e-88f2-737e504e7b95',
                        'supplier_code',
                        'Supplier label',
                    ),
                )
                ->build(),
        );
    }

    /**
     * @Given a product file with 50 retailer comments
     */
    public function aProductFileWithFiftyRetailerComments(): void
    {
        $this->productFileIdentifier = '893e5eab-d85c-4c47-9c4f-3afc17d6b1eb';

        $productFile = (new ProductFileBuilder())
            ->withIdentifier($this->productFileIdentifier)
            ->uploadedBySupplier(
                new Supplier(
                    'f7555f61-2ea6-4b0e-88f2-737e504e7b95',
                    'supplier_code',
                    'Supplier label',
                ),
            )
            ->build();

        for ($i = 0; 50 > $i; $i++) {
            $productFile->addNewRetailerComment('foo', 'julia@roberts.com', new \DateTimeImmutable());
        }

        $this->productFileRepository->save($productFile);
    }

    /**
     * @Given a product file with 50 supplier comments
     */
    public function aProductFileWithFiftySupplierComments(): void
    {
        $this->productFileIdentifier = '893e5eab-d85c-4c47-9c4f-3afc17d6b1eb';

        $productFile = (new ProductFileBuilder())
            ->withIdentifier($this->productFileIdentifier)
            ->uploadedBySupplier(
                new Supplier(
                    'f7555f61-2ea6-4b0e-88f2-737e504e7b95',
                    'supplier_code',
                    'Supplier label',
                ),
            )
            ->build();

        for ($i = 0; 50 > $i; $i++) {
            $productFile->addNewSupplierComment('foo', 'jimmy@supplier.com', new \DateTimeImmutable());
        }

        $this->productFileRepository->save($productFile);
    }

    /**
     * @Given supplier contributors have not read the comments yet
     */
    public function supplierContributorsHaveNotReadTheCommentsYet(): void
    {
        $productFile = $this->productFileRepository->findProductFileWithUnreadCommentsFromRetailer(
            Identifier::fromString($this->productFileIdentifier),
        );
        Assert::assertNull($productFile['commentslastReadDate']);
    }

    /**
     * @Given retailers have not read the comments yet
     */
    public function retailersHaveNotReadTheCommentsYet(): void
    {
        $productFile = $this->productFileRepository->findProductFileWithUnreadCommentsFromSupplier(
            Identifier::fromString($this->productFileIdentifier),
        );
        Assert::assertNull($productFile['commentslastReadDate']);
    }

    /**
     * @When a retailer comments it with :content
     */
    public function aRetailerCommentsItWith(string $content): void
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
     * @When a supplier comments it with :content
     */
    public function aSupplierCommentsItWith(string $content): void
    {
        try {
            ($this->commentProductFileHandlerForSupplier)(new CommentProductFileForSupplier(
                $this->productFileIdentifier,
                'jimmy@megasupplier.com',
                $content,
                new \DateTimeImmutable(),
            ));
        } catch (\Exception $e) {
            $this->exception = $e;
        }
    }

    /**
     * @When a retailer comments it with a too long comment
     */
    public function aRetailerCommentsItWithATooLongComment(): void
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
     * @When a supplier comments it with a too long comment
     */
    public function aSupplierCommentsItWithATooLongComment(): void
    {
        try {
            ($this->commentProductFileHandlerForSupplier)(new CommentProductFileForSupplier(
                $this->productFileIdentifier,
                'jimmy@megasupplier.com',
                str_repeat('q', 256),
                new \DateTimeImmutable(),
            ));
        } catch (\Exception $e) {
            $this->exception = $e;
        }
    }

    /**
     * @When a supplier contributor marks all comments of a product file as read
     */
    public function aSupplierContributorMarksAllCommentsAsRead(): void
    {
        ($this->markCommentsAsReadBySupplierHandler)(
            new MarkCommentsAsReadBySupplier($this->productFileIdentifier, new \DateTimeImmutable())
        );
    }

    /**
     * @When a retailer marks all comments of a product file as read
     */
    public function aRetailerMarksAllCommentsAsRead(): void
    {
        ($this->markCommentsAsReadByRetailerHandler)(
            new MarkCommentsAsReadByRetailer($this->productFileIdentifier, new \DateTimeImmutable())
        );
    }

    /**
     * @When a supplier contributor try to mark comments of an unknown product file as read
     */
    public function aSupplierContributorTryToMarkCommentsOfAnUnknownProductFileAsRead(): void
    {
        try {
            ($this->markCommentsAsReadBySupplierHandler)(
                new MarkCommentsAsReadBySupplier('f7555f61-2ea6-4b0e-88f2-737e504e7b95', new \DateTimeImmutable())
            );
        } catch (ProductFileDoesNotExist $e) {
            $this->exception = $e;
        }
    }

    /**
     * @When a retailer try to mark comments of an unknown product file as read
     */
    public function aRetailerTryToMarkCommentsOfAnUnknownProductFileAsRead(): void
    {
        try {
            ($this->markCommentsAsReadByRetailerHandler)(
                new MarkCommentsAsReadByRetailer('f7555f61-2ea6-4b0e-88f2-737e504e7b95', new \DateTimeImmutable())
            );
        } catch (ProductFileDoesNotExist $e) {
            $this->exception = $e;
        }
    }

    /**
     * @Then it should have throw an exception
     */
    public function itShouldHavethrowAProductFileDoesNotExistException(): void
    {
        Assert::assertInstanceOf(ProductFileDoesNotExist::class, $this->exception);
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
     * @Then the product file contains the retailer comment :content
     */
    public function theProductFileContainsTheRetailerComment(string $content): void
    {
        $productFile = ($this->getProductFileWithMedataAndComments)($this->productFileIdentifier);

        Assert::assertCount(1, $productFile->retailerComments);
        Assert::assertSame($content, $productFile->retailerComments[0]->content());
    }

    /**
     * @Then the product file contains the supplier comment :content
     */
    public function theProductFileContainsTheSupplierComment(string $content): void
    {
        $productFile = ($this->getProductFileWithMedataAndComments)($this->productFileIdentifier);

        Assert::assertCount(1, $productFile->supplierComments);
        Assert::assertSame($content, $productFile->supplierComments[0]->content());
    }

    /**
     * @Then all retailer comments should be marked as read for this product file
     */
    public function allRetailerCommentsShouldBeMarkedAsRead(): void
    {
        $productFile = $this->productFileRepository->findProductFileWithUnreadCommentsFromRetailer(
            Identifier::fromString($this->productFileIdentifier),
        );
        Assert::assertInstanceOf(\DateTimeImmutable::class, $productFile['commentslastReadDate']);
    }

    /**
     * @Then all supplier comments should be marked as read for this product file
     */
    public function allSupplierCommentsShouldBeMarkedAsRead(): void
    {
        $productFile = $this->productFileRepository->findProductFileWithUnreadCommentsFromSupplier(
            Identifier::fromString($this->productFileIdentifier),
        );
        Assert::assertInstanceOf(\DateTimeImmutable::class, $productFile['commentslastReadDate']);
    }

    /**
     * @Then retailer comments shouldn't be marked as read for this product file
     */
    public function retailerCommentsShouldNotBeMarkedAsRead(): void
    {
        $productFile = $this->productFileRepository->findProductFileWithUnreadCommentsFromRetailer(
            Identifier::fromString($this->productFileIdentifier),
        );
        Assert::assertNull($productFile['commentslastReadDate']);
    }

    /**
     * @Then supplier comments shouldn't be marked as read for this product file
     */
    public function supplierCommentsShouldNotBeMarkedAsRead(): void
    {
        $productFile = $this->productFileRepository->findProductFileWithUnreadCommentsFromSupplier(
            Identifier::fromString($this->productFileIdentifier),
        );
        Assert::assertNull($productFile['commentslastReadDate']);
    }
}
