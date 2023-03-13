<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command\CleanCategoryEnrichedValuesByTemplateUuid;

use Akeneo\Category\Application\Enrichment\CategoryDataCleaner;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CleanCategoryEnrichedValuesByTemplateUuidCommandHandler
{
    public function __construct(
        private readonly CategoryDataCleaner $categoryDataCleaner,
    ) {
    }

    public function __invoke(CleanCategoryEnrichedValuesByTemplateUuidCommand $command): void
    {

    }
}
