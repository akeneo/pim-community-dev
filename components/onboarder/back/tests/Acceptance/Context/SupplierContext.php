<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Acceptance\Context;

use Akeneo\OnboarderSerenity\Domain\Write\Supplier\Repository;
use Behat\Behat\Context\Context;

final class SupplierContext implements Context
{
    public function __construct(
        private Repository $supplierRepository,
    ) {
    }

    /**
     * @Given there is no supplier
     */
    public function thereIsNoSupplier(): void
    {
        //Check if there are suppliers. Move method count() in the repository ?
    }

    /**
     * @When I create a supplier with code ":code" and label ":label"
     */
    public function iCreateASupplierWithACodeAndALabel(string $code, string $label)
    {
        dump($code, $label);
    }

    /**
     * @Then I should have a supplier with code ":code" and label ":label"
     */
    public function iShouldHaveASupplierWithCodeAndLabel(string $code, string $label)
    {
        dump($code, $label);
    }
}
