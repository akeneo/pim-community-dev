<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Update;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Query\ReorderIdentifierGenerators;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ReorderGeneratorsHandler
{
    public function __construct(private readonly ReorderIdentifierGenerators $reorderIdentifierGenerators)
    {
    }

    public function __invoke(ReorderGeneratorsCommand $command): void
    {
        $this->reorderIdentifierGenerators->byCodes($command->codes);
    }
}
