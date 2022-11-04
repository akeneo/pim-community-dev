<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Acceptance\Context;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileImport\Read\Model\ProductFileImportConfiguration;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileImport\ServiceApi\InMemory\InMemoryFindAllProductFileImportConfigurations;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;

final class ProductFileImportContext implements Context
{
    private array $productFileImports = [];

    public function __construct(
        private InMemoryFindAllProductFileImportConfigurations $findAllProductFileImportProfiles,
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
}
