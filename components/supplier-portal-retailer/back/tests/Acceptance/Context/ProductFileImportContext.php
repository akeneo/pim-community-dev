<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Acceptance\Context;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileImport\Write\ImportProductFile\ImportProductFile;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileImport\Write\ImportProductFile\ImportProductFileHandler;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Read\Model\ProductFileImportConfiguration;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Write\Model\ProductFileImport;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Repository\InMemory\InMemoryRepository as ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileImport\Repository\InMemory\InMemoryRepository as ProductFileImportRepository;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileImport\ServiceApi\InMemory\InMemoryFindAllProductFileImportConfigurations;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

final class ProductFileImportContext implements Context
{
    private array $productFileImports = [];
    private string $redirectionUrlAfterImport = '';
    private ?\Exception $exception = null;

    public function __construct(
        private InMemoryFindAllProductFileImportConfigurations $findAllProductFileImportProfiles,
        private ImportProductFileHandler $importProductFileHandler,
        private ProductFileImportRepository $productFileImportRepository,
        private ProductFileRepository $productFileRepository,
    ) {
    }

    /**
     * @Given there is no product file import configuration
     */
    public function thereIsNoProductFileImportConfiguration(): void
    {
        Assert::assertEmpty(($this->findAllProductFileImportProfiles)());
    }

    /**
     * @Given there is a product file import configuration ":code"
     */
    public function thereIsAProductFileImportConfiguration(string $code): void
    {
        $this->findAllProductFileImportProfiles->add(new ProductFileImportConfiguration($code, $code));
    }

    /**
     * @When I retrieve the product file import configurations
     */
    public function iRetrieveTheProductFileImportConfigurations(): void
    {
        $this->productFileImports = ($this->findAllProductFileImportProfiles)();
    }

    /**
     * @When I import the product file :fileName
     */
    public function iImportTheProductFile(string $fileName): void
    {
        $productFile = $this->productFileRepository->findByName($fileName);
        try {
            $this->redirectionUrlAfterImport = ($this->importProductFileHandler)(
                new ImportProductFile(
                    'import1',
                    null !== $productFile ? $productFile->identifier() : Uuid::uuid4()->toString(),
                )
            );
        } catch (ProductFileDoesNotExist $e) {
            $this->exception = $e;
        }
    }

    /**
     * @Then I should have an empty list of product file import configurations
     */
    public function iShouldHaveAnEmptyList(): void
    {
        Assert::assertEmpty($this->productFileImports);
    }

    /**
     * @Then I should have the product file import configurations :codes
     */
    public function iShouldHaveTheProductFileImportConfigurations(string $codes): void
    {
        $expectedCodes = array_map('trim', explode(',', $codes));
        $actualCodes = array_map(
            fn (ProductFileImportConfiguration $productImportProfile) => $productImportProfile->toArray()['code'],
            $this->productFileImports,
        );
        Assert::assertSame($expectedCodes, $actualCodes);
    }

    /**
     * @Then I should be redirected to :url
     */
    public function iShouldBeRedirectedTo(string $url): void
    {
        Assert::assertSame($url, $this->redirectionUrlAfterImport);
        $productFileImport = $this->productFileImportRepository->find('893e5eab-d85c-4c47-9c4f-3afc17d6b1eb');
        Assert::assertInstanceOf(ProductFileImport::class, $productFileImport);
    }

    /**
     * @Then I should have a product file does not exist error
     */
    public function iShouldHaveAProductFileDoesNotExistError(): void
    {
        Assert::assertInstanceOf(ProductFileDoesNotExist::class, $this->exception);
    }
}
