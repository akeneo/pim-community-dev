<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\UserIntent\Factory;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface UserIntentFactory
{
    /**
     * @return string[]
     */
    public function getSupportedFieldNames(): array;

    /**
     * @return UserIntent[]
     */
    public function create(string $fieldName, mixed $data): array;
}
