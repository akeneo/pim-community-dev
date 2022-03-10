<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Acceptance\Context;

use Akeneo\OnboarderSerenity\Application\Supplier\CreateSupplier;
use Akeneo\OnboarderSerenity\Application\Supplier\CreateSupplierHandler;
use Akeneo\OnboarderSerenity\Domain\Write\Supplier;
use Akeneo\OnboarderSerenity\Infrastructure\Supplier\InMemoryRepository;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

final class SupplierContext implements Context
{
    private string $supplierIdentifier;

    private ?\Exception $exception;

    public function __construct(
        private InMemoryRepository $supplierRepository,
        private CreateSupplierHandler $createSupplierHandler,
    ) {
        $this->supplierIdentifier = Uuid::uuid4()->toString();
    }

    /**
     * @Given there is no supplier
     */
    public function thereIsNoSupplier(): void
    {
        Assert::assertSame(0, $this->supplierRepository->count());
    }

    /**
     * @When I create a supplier with code ":code" and label ":label"
     * @When I create a supplier with code ":code"
     */
    public function iCreateASupplierWithACodeAndALabel(string $code, ?string $label = null): void
    {
        try {
            ($this->createSupplierHandler)(new CreateSupplier($this->supplierIdentifier, $code, $label ?: $code));
        } catch (Supplier\Exception\SupplierAlreadyExistsException $e) {
            $this->exception = $e;
        }
    }

    /**
     * @Then I should have a supplier with code ":code" and label ":label"
     */
    public function iShouldHaveASupplierWithCodeAndLabel(string $code, string $label): void
    {
        $supplier = $this->supplierRepository->find(
            Supplier\ValueObject\Identifier::fromString($this->supplierIdentifier)
        );

        Assert::assertSame($code, $supplier->code());
        Assert::assertSame($label, $supplier->label());
    }

    /**
     * @Given a supplier ":code"
     */
    public function aSupplier(string $code): void
    {
        $this->supplierRepository->save(Supplier\Model\Supplier::create(
            $this->supplierIdentifier,
            $code,
            $code
        ));
    }

    /**
     * @Then an error is thrown because this supplier already exists
     */
    public function aSupplierAlreadyExistsExceptionShouldBeThrown(): void
    {
        Assert::assertInstanceOf(Supplier\Exception\SupplierAlreadyExistsException::class, $this->exception);
    }
}
