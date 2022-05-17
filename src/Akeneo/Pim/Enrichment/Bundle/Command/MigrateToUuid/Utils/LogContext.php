<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\Utils;

use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\MigrateToUuidStep;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LogContext
{
    private array $context = [];

    public function __construct(private MigrateToUuidStep $step)
    {
    }

    public function toArray(array $extraContext = []): array
    {
        return array_filter(array_merge(
            $this->context,
            $extraContext,
            ['step' => $this->step->getName(), 'step_status' => $this->step->getStatus(), 'step_duration' => $this->step->getDuration()]
        ));
    }

    public function addContext(string $key, ?string $value): void
    {
        $this->context[$key] = $value;
    }
}
