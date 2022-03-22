<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Acceptance\Context;

use Akeneo\OnboarderSerenity\Application\Supplier\CreateSupplier;
use Akeneo\OnboarderSerenity\Application\Supplier\CreateSupplierHandler;
use Akeneo\OnboarderSerenity\Application\Supplier\DeleteSupplier;
use Akeneo\OnboarderSerenity\Application\Supplier\DeleteSupplierHandler;
use Akeneo\OnboarderSerenity\Domain\Read;
use Akeneo\OnboarderSerenity\Domain\Write;
use Akeneo\OnboarderSerenity\Infrastructure\Supplier\Query\InMemory\InMemoryGetSupplierList;
use Akeneo\OnboarderSerenity\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;
use Akeneo\OnboarderSerenity\Infrastructure\Supplier\Contributor;
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
        private InMemoryGetSupplierList $getSupplierList,
        private DeleteSupplierHandler $deleteSuppliersHandler,
        private Contributor\Repository\InMemory\InMemoryRepository $contributorRepository,
    ) {
        $this->suppliers = [];
    }

    /**
     * @Given there is no supplier
     */
    public function thereIsNoSupplier(): void
    {
        Assert::assertSame(0, $this->supplierRepository->count());
    }

    /**
     * @Given a supplier with code ":code" and label ":label" and ":contributorsCount" contributors
     * @Given a supplier with code ":code" and label ":label"
     * @Given a supplier ":code"
     */
    public function thereIsASupplier(string $code, ?string $label = null, ?int $contributorsCount = null): void
    {
        $supplierIdentifier = Uuid::uuid4()->toString();
        $this->supplierRepository->save(Write\Supplier\Model\Supplier::create(
            $supplierIdentifier,
            $code,
            $label ?: $code,
        ));

        if ($contributorsCount !== null) {
            for ($i = 0; $i < $contributorsCount; $i++) {
                $this->contributorRepository->save(Write\Supplier\Contributor\Model\Contributor::create(
                    Uuid::uuid4()->toString(),
                    'email'.$i.'@akeneo.com',
                    $supplierIdentifier,
                ));
            }
        }
    }

    /**
     * @When I create a supplier with code ":code" and label ":label"
     * @When I create a supplier with code ":code"
     */
    public function iCreateASupplierWithACodeAndALabel(string $code, ?string $label = null): void
    {
        try {
            ($this->createSupplierHandler)(new CreateSupplier(Uuid::uuid4()->toString(), $code, $label ?: $code));
        } catch (Write\Supplier\Exception\SupplierAlreadyExistsException $e) {
            $this->lastException = $e;
        }
    }

    /**
     * @When I retrieve the suppliers
     */
    public function iRetrieveSuppliers(): void
    {
        $this->loadSuppliers();
    }

    /**
     * @When I search on :search
     */
    public function iSearchOn(string $search): void
    {
        $this->loadSuppliers($search);
    }

    /**
     * @When I delete the supplier ":code"
     */
    public function iDeleteTheSupplier(string $code)
    {
        $supplier = $this->supplierRepository->findByCode(Write\Supplier\ValueObject\Code::fromString($code));
        ($this->deleteSuppliersHandler)(new DeleteSupplier($supplier->identifier()));
    }

    /**
     * @Then I should have a supplier with code ":code" and label ":label"
     */
    public function iShouldHaveASupplierWithCodeAndLabel(string $code, string $label): void
    {
        $supplier = $this->supplierRepository->findByCode(
            Write\Supplier\ValueObject\Code::fromString($code)
        );

        Assert::assertSame($code, $supplier->code());
        Assert::assertSame($label, $supplier->label());
    }

    /**
     * @Then an error is thrown because this supplier already exists
     */
    public function aSupplierAlreadyExistsExceptionShouldBeThrown(): void
    {
        Assert::assertInstanceOf(Write\Supplier\Exception\SupplierAlreadyExistsException::class, $this->lastException);
    }

    /**
     * @Then I should have the following suppliers:
     */
    public function iShouldHaveTheFollowingSuppliers(TableNode $properties): void
    {
        if (0 === \count($this->suppliers)) {
            $this->loadSuppliers();
        }

        $expectedSuppliers = $properties->getHash();
        $actualSuppliers = array_map(fn (Read\Supplier\Model\SupplierListItem $supplier) => [
            'code' => $supplier->code,
            'label' => $supplier->label,
            'contributor_count' => $supplier->contributorsCount
        ], $this->suppliers);

        Assert::assertEquals($expectedSuppliers, array_values($actualSuppliers));
    }

    private function loadSuppliers($search = ''): void
    {
        $this->suppliers = ($this->getSupplierList)(1, $search);
    }
}
