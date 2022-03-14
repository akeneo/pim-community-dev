<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Acceptance\Context;

use Akeneo\OnboarderSerenity\Application\Supplier\CreateSupplier;
use Akeneo\OnboarderSerenity\Application\Supplier\CreateSupplierHandler;
use Akeneo\OnboarderSerenity\Application\Supplier\GetSuppliers;
use Akeneo\OnboarderSerenity\Application\Supplier\GetSuppliersHandler;
use Akeneo\OnboarderSerenity\Domain\Read;
use Akeneo\OnboarderSerenity\Domain\Write\Supplier;
use Akeneo\OnboarderSerenity\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

final class SupplierContext implements Context
{
    private ?\Exception $lastException;

    private array $suppliers;

    public function __construct(
        private InMemoryRepository $supplierRepository,
        private CreateSupplierHandler $createSupplierHandler,
        private GetSuppliersHandler $getSuppliersHandler,
    ) {
    }

    /**
     * @Given there is no supplier
     */
    public function thereIsNoSupplier(): void
    {
        Assert::assertSame(0, $this->supplierRepository->count());
    }

    /**
     * @Given there is a supplier with code ":code" and label ":label"
     * @Given a supplier ":code"
     */
    public function thereIsASupplier(string $code, ?string $label = null): void
    {
        $this->supplierRepository->save(Supplier\Model\Supplier::create(
            Uuid::uuid4()->toString(),
            $code,
            $label ?: $code,
        ));
    }

    /**
     * @When I create a supplier with code ":code" and label ":label"
     * @When I create a supplier with code ":code"
     */
    public function iCreateASupplierWithACodeAndALabel(string $code, ?string $label = null): void
    {
        try {
            ($this->createSupplierHandler)(new CreateSupplier(Uuid::uuid4()->toString(), $code, $label ?: $code));
        } catch (Supplier\Exception\SupplierAlreadyExistsException $e) {
            $this->lastException = $e;
        }
    }

    /**
     * @When I retrieve the suppliers
     */
    public function iRetrieveSuppliers(): void
    {
        $this->suppliers = ($this->getSuppliersHandler)(new GetSuppliers());
    }

    /**
     * @Then I should have a supplier with code ":code" and label ":label"
     */
    public function iShouldHaveASupplierWithCodeAndLabel(string $code, string $label): void
    {
        $supplier = $this->supplierRepository->findByCode(
            Supplier\ValueObject\Code::fromString($code)
        );

        Assert::assertSame($code, $supplier->code());
        Assert::assertSame($label, $supplier->label());
    }

    /**
     * @Then an error is thrown because this supplier already exists
     */
    public function aSupplierAlreadyExistsExceptionShouldBeThrown(): void
    {
        Assert::assertInstanceOf(Supplier\Exception\SupplierAlreadyExistsException::class, $this->lastException);
    }

    /**
     * @Then I should have the following suppliers:
     */
    public function iShouldHaveTheFollowingSuppliers(TableNode $properties): void
    {
        $expectedSuppliers = $properties->getHash();
        $actualSuppliers = array_map(fn (Read\Supplier\Model\Supplier $supplier) => ['code' => $supplier->code, 'label' => $supplier->label], $this->suppliers);

        Assert::assertSame($expectedSuppliers, array_values($actualSuppliers));
    }
}
