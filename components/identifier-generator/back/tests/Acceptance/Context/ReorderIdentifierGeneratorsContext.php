<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Acceptance\Context;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\ReorderGeneratorsCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Update\ReorderGeneratorsHandler;
use Behat\Behat\Context\Context;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ReorderIdentifierGeneratorsContext implements Context
{
    public function __construct(
        private readonly ReorderGeneratorsHandler $reorderGeneratorsHandler
    ) {
    }

    /**
     * @When /^I reorder the identifier generators as (?P<codes>(('.*')(, | and )?)+)$/
     */
    public function iReorderTheIdentifierGenerators(string $codes): void
    {
        ($this->reorderGeneratorsHandler)(ReorderGeneratorsCommand::fromCodes(CodesSplitter::split($codes)));
    }
}
