<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Acceptance\Context;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Read\Model\ProductFileImport;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileImport\ServiceApi\InMemoryFindAllProductFileImportProfiles;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;

final class ProductFileImportContext implements Context
{
    private array $productFileImports = [];

    public function __construct(
        private InMemoryFindAllProductFileImportProfiles $findAllProductFileImportProfiles,
    ) {
    }

    /**
     * @Given there is no product file imports
     */
    public function thereIsNoProductFileImports(): void
    {
        Assert::assertEmpty(($this->findAllProductFileImportProfiles)());
    }

    /**
     * @Given there is a product file import ":code"
     */
    public function thereIsAProductFileImport(string $code): void
    {
        $this->findAllProductFileImportProfiles->add(new ProductFileImport($code, $code));
    }

    /**
     * @When I retrieve the product file imports
     */
    public function iRetrieveTheProductFileImports(): void
    {
        $this->productFileImports = ($this->findAllProductFileImportProfiles)();
    }

    /**
     * @Then I should have an empty list
     */
    public function iShouldHaveAnEmptyList(): void
    {
        Assert::assertEmpty($this->productFileImports);
    }

    /**
     * @Then I should have the product file imports :codes
     */
    public function iShouldHaveTheProductFileImports(string $codes): void
    {
        $expectedCodes = array_map('trim', explode(',', $codes));
        $actualCodes = array_map(
            fn (ProductFileImport $productImportProfile) => $productImportProfile->toArray()['code'],
            $this->productFileImports,
        );
        Assert::assertSame($expectedCodes, $actualCodes);
    }
}
