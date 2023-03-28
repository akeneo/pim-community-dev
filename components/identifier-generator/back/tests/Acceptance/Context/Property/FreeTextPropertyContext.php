<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Acceptance\Context\Property;

use Akeneo\Test\Pim\Automation\IdentifierGenerator\Acceptance\Context\BaseCreateOrUpdateIdentifierGenerator;
use Behat\Behat\Context\Context;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FreeTextPropertyContext extends BaseCreateOrUpdateIdentifierGenerator implements Context
{
    /**
     * @When /^I try to create an identifier generator with free text '(?P<freetextContent>[^']*)'$/
     */
    public function iTryToCreateAnIdentifierGeneratorWithFreeText(string $freetextContent): void
    {
        $this->tryToCreateGenerator(structure: [['type' => 'free_text', 'string' => $freetextContent]]);
    }

    /**
     * @When I try to create an identifier generator with free text without required field
     */
    public function iCreateAnIdentifierGeneratorWithFreeTextWithoutRequiredField(): void
    {
        $this->tryToCreateGenerator(structure: [['type' => 'free_text']]);
    }

    /**
     * @When I try to create an identifier generator with free text with unknown field
     */
    public function iTryToCreateAnIdentifierGeneratorWithFreeTextWithUnknownField(): void
    {
        $this->tryToCreateGenerator(structure: [['type' => 'free_text', 'unknown' => 'hello', 'string' => 'hey']]);
    }

    /**
     * @When /^I try to update an identifier generator with free text '(?P<freetextContent>[^']*)'$/
     */
    public function iTryToUpdateAnIdentifierGeneratorWithFreeText(string $freetextContent): void
    {
        $this->tryToUpdateGenerator(structure: [['type' => 'free_text', 'string' => $freetextContent]]);
    }

    /**
     * @When I try to update an identifier generator with free text without required field
     */
    public function iTryToUpdateAnIdentifierGeneratorWithFreeTextWithoutRequiredField(): void
    {
        $this->tryToUpdateGenerator(structure: [['type' => 'free_text']]);
    }

    /**
     * @When I try to update an identifier generator with free text with unknown field
     */
    public function iTryToUpdateAnIdentifierGeneratorWithFreeTextWithUnknownField(): void
    {
        $this->tryToUpdateGenerator(structure: [['type' => 'free_text', 'unknown' => 'hello', 'string' => 'hey']]);
    }
}
