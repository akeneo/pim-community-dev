<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Acceptance\Context\Condition;

use Akeneo\Test\Pim\Automation\IdentifierGenerator\Acceptance\Context\BaseCreateOrUpdateIdentifierGenerator;
use Behat\Behat\Context\Context;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class EnabledConditionContext extends BaseCreateOrUpdateIdentifierGenerator implements Context
{
    /**
     * @When I try to create an identifier generator with 2 enabled conditions
     */
    public function iTryToCreateAnIdentifierGeneratorWith2EnabledConditions(): void
    {
        $this->tryToCreateGenerator(conditions: [
            ['type' => 'enabled', 'value' => true],
            ['type' => 'enabled', 'value' => true],
        ]);
    }

    /**
     * @When I try to update an identifier generator with :arg1 enabled conditions
     */
    public function iTryToUpdateAnIdentifierGeneratorWithEnabledConditions($arg1): void
    {
        $this->tryToUpdateGenerator(conditions: [
            ['type' => 'enabled', 'value' => true],
            ['type' => 'enabled', 'value' => true],
        ]);
    }
}
