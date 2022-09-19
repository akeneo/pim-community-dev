<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LabelCollection
{
    /**
     * @param array<string, string> $labels
     */
    private function __construct(
        private array $labels,
    )
    {
    }
}
