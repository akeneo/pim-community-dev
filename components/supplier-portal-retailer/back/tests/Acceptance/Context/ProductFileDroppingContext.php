<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Acceptance\Context;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\CommentProductFile;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\CommentProductFileHandler;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Exception\InvalidComment;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFileWithComments;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Repository\InMemory\InMemoryRepository as InMemoryProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository as InMemorySupplierRepository;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;

final class ProductFileDroppingContext implements Context
{
    private string $productFileIdentifier;
    private array $errors = [];

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
        $this->supplierRepository->save(Write\Model\Supplier::create(
            'f7555f61-2ea6-4b0e-88f2-737e504e7b95',
            'los_pollos_hermanos',
            'Los Pollos Hermanos',
            [],
        ));
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
                'los_pollos_hermanos',
                'Los Pollos Hermanos',
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
                'los_pollos_hermanos',
                'Los Pollos Hermanos',
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
        } catch (InvalidComment $e) {
            $this->normalizeValidationErrors($e);
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
        } catch (InvalidComment $e) {
            $this->normalizeValidationErrors($e);
        }
    }

    /**
     * @Then I should have the following comment validation errors:
     */
    public function iShouldHaveTheFollowingCommentValidationErrors(TableNode $table): void
    {
        Assert::assertEquals($table->getHash(), $this->errors);
    }

    private function normalizeValidationErrors(InvalidComment $e): void
    {
        $errors = [];
        foreach ($e->violations() as $violation) {
            $errors[] = [
                'path' => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
            ];
        }
        $this->errors = $errors;
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
