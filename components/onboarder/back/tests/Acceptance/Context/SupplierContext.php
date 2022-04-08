<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Acceptance\Context;

use Akeneo\OnboarderSerenity\Application\Supplier\CreateSupplier;
use Akeneo\OnboarderSerenity\Application\Supplier\CreateSupplierHandler;
use Akeneo\OnboarderSerenity\Application\Supplier\DeleteSupplier;
use Akeneo\OnboarderSerenity\Application\Supplier\DeleteSupplierHandler;
use Akeneo\OnboarderSerenity\Application\Supplier\Exception\InvalidData;
use Akeneo\OnboarderSerenity\Application\Supplier\UpdateSupplier;
use Akeneo\OnboarderSerenity\Application\Supplier\UpdateSupplierHandler;
use Akeneo\OnboarderSerenity\Domain\Read;
use Akeneo\OnboarderSerenity\Domain\Read\Supplier\GetSupplier;
use Akeneo\OnboarderSerenity\Domain\Write;
use Akeneo\OnboarderSerenity\Infrastructure\Supplier\Query\InMemory\InMemoryGetSupplierList;
use Akeneo\OnboarderSerenity\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;

final class SupplierContext implements Context
{
    private ?\Exception $lastException;

    private array $suppliers;

    private ?Read\Supplier\Model\Supplier $supplier;

    private array $errors;

    public function __construct(
        private InMemoryRepository $supplierRepository,
        private CreateSupplierHandler $createSupplierHandler,
        private InMemoryGetSupplierList $getSupplierList,
        private DeleteSupplierHandler $deleteSuppliersHandler,
        private GetSupplier $getSupplier,
        private UpdateSupplierHandler $updateSupplierHandler,
    ) {
        $this->suppliers = [];
        $this->supplier = null;
        $this->errors = [];
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

        $contributorEmails = [];
        for ($i = 1; $i <= $contributorsCount; $i++) {
            $contributorEmails[] = 'email'.$i.'@example.com';
        }

        $this->supplierRepository->save(Write\Supplier\Model\Supplier::create(
            $supplierIdentifier,
            $code,
            $label ?: $code,
            $contributorEmails,
        ));
    }

    /**
     * @When I create a supplier with code ":code" and label ":label"
     * @When I create a supplier with code ":code"
     */
    public function iCreateASupplierWithACodeAndALabel(string $code, ?string $label = null): void
    {
        try {
            ($this->createSupplierHandler)(new CreateSupplier(Uuid::uuid4()->toString(), $code, $label ?: $code, []));
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
    public function iDeleteTheSupplier(string $code): void
    {
        $supplier = $this->supplierRepository->findByCode(Write\Supplier\ValueObject\Code::fromString($code));
        ($this->deleteSuppliersHandler)(new DeleteSupplier($supplier->identifier()));
    }

    /**
     * @When I retrieve the supplier ":code"
     */
    public function iRetrieveTheSupplier(string $code): void
    {
        $this->loadSupplier($code);
    }

    /**
     * @When I update the supplier ":code" label with ":label"
     */
    public function iUpdateTheSupplierLabelWith(string $code, string $label): void
    {
        $supplier = $this->supplierRepository->findByCode(Write\Supplier\ValueObject\Code::fromString($code));
        ($this->updateSupplierHandler)(new UpdateSupplier($supplier->identifier(), $label, $supplier->contributors()));
    }

    /**
     * @When I update the supplier ":code" with a label longer than 200 characters
     */
    public function iUpdateTheSupplierWithALabelLongerThan200Characters(string $code): void
    {
        $supplier = $this->supplierRepository->findByCode(Write\Supplier\ValueObject\Code::fromString($code));

        try {
            ($this->updateSupplierHandler)(
                new UpdateSupplier(
                    $supplier->identifier(),
                    str_repeat('a', 201),
                    $supplier->contributors(),
                )
            );
        } catch (InvalidData $e) {
            $this->normalizeValidationErrors($e);
        }
    }

    /**
     * @When I update the supplier ":code" with a blank label
     */
    public function iUpdateTheSupplierWithABlankLabel(string $code): void
    {
        $supplier = $this->supplierRepository->findByCode(Write\Supplier\ValueObject\Code::fromString($code));

        try {
            ($this->updateSupplierHandler)(
                new UpdateSupplier(
                    $supplier->identifier(),
                    '',
                    $supplier->contributors(),
                )
            );
        } catch (InvalidData $e) {
            $this->normalizeValidationErrors($e);
        }
    }

    /**
     * @When I update the supplier ":code" with an email address longer than 255 for a contributor
     */
    public function iUpdateTheSupplierWithATooLongEmailAddressLongerThan255ForContributor(string $code): void
    {
        $supplier = $this->supplierRepository->findByCode(Write\Supplier\ValueObject\Code::fromString($code));
        $longEmail = str_repeat('a', 250) . '@' . 'aa.co';

        try {
            ($this->updateSupplierHandler)(
                new UpdateSupplier(
                    $supplier->identifier(),
                    $supplier->label(),
                    [$longEmail],
                )
            );
        } catch (InvalidData $e) {
            $this->normalizeValidationErrors($e);
        }
    }

    /**
     * @When I update the supplier ":code" contributors with ":contributors"
     */
    public function iUpdateTheSupplierContributorsWith(string $code, string $contributors): void
    {
        $supplier = $this->supplierRepository->findByCode(Write\Supplier\ValueObject\Code::fromString($code));
        try {
            ($this->updateSupplierHandler)(
                new UpdateSupplier(
                    $supplier->identifier(),
                    $supplier->label(),
                    '' !== $contributors ? explode(';', $contributors) : [],
                )
            );
        } catch (InvalidData $e) {
            $this->normalizeValidationErrors($e);
        }
    }

    /**
     * @Then I should have a supplier with code ":code" and label ":label"
     */
    public function iShouldHaveASupplierWithCodeAndLabel(string $code, string $label): void
    {
        $supplier = $this->supplierRepository->findByCode(
            Write\Supplier\ValueObject\Code::fromString($code),
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
            'contributor_count' => $supplier->contributorsCount,
        ], $this->suppliers);

        Assert::assertEquals($expectedSuppliers, array_values($actualSuppliers));
    }

    /**
     * @Then I should have a supplier with code ":code" and contributors ":contributors"
     */
    public function iShouldHaveASupplierWithCodeAndContributors(string $code, ?string $contributors): void
    {
        $supplier = $this->supplierRepository->findByCode(
            Write\Supplier\ValueObject\Code::fromString($code),
        );

        $contributors = '' !== $contributors ? explode(';', $contributors) : [];
        $contributors = array_map(fn ($contributor) => ['email' => $contributor], $contributors);

        Assert::assertSame($code, $supplier->code());
        Assert::assertSame($contributors, $supplier->contributors());
    }

    /**
     * @Then I should have the following validation errors:
     */
    public function iShouldHaveTheFollowingValidationErrors(TableNode $table): void
    {
        Assert::assertEquals($table->getHash(), $this->errors);
    }

    private function loadSuppliers(string $search = ''): void
    {
        $this->suppliers = ($this->getSupplierList)(1, $search);
    }

    private function loadSupplier(string $code): void
    {
        $supplier = $this->supplierRepository->findByCode(Write\Supplier\ValueObject\Code::fromString($code));
        $this->supplier = ($this->getSupplier)(Write\Supplier\ValueObject\Identifier::fromString($supplier->identifier()));
    }

    private function normalizeValidationErrors(InvalidData $e): void
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
}
