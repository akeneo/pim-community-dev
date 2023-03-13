<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Enrichment\Filter;

use Akeneo\Category\Domain\ValueObject\ValueCollection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ByTemplateUuidFilter
{
    public static function getEnrichedValuesToClean(
        ValueCollection $enrichedValues,
        string $templateUuid,
    ): array {
        return [];
    }
}
