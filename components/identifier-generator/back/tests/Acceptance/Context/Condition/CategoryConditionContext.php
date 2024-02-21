<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Acceptance\Context\Condition;

use Akeneo\Test\Pim\Automation\IdentifierGenerator\Acceptance\Context\BaseCreateOrUpdateIdentifierGenerator;
use Behat\Behat\Context\Context;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CategoryConditionContext extends BaseCreateOrUpdateIdentifierGenerator implements Context
{
    /**
     * @When I try to create an identifier generator with 2 category conditions
     */
    public function iTryToCreateAnIdentifierGeneratorWith2CategoryConditions(): void
    {
        $this->tryToCreateGenerator(conditions: [
            ['type' => 'category', 'operator' => 'IN', 'value' => ['tshirts']],
            ['type' => 'category', 'operator' => 'NOT IN', 'value' => ['shoes']],
        ]);
    }

    /**
     * @When I try to update an identifier generator with 2 category conditions
     */
    public function iTryToUpdateAnIdentifierGeneratorWith2CategoryConditions(): void
    {
        $this->tryToUpdateGenerator(conditions: [
            ['type' => 'category', 'operator' => 'IN', 'value' => ['tshirts']],
            ['type' => 'category', 'operator' => 'NOT IN', 'value' => ['shoes']],
        ]);
    }
}
