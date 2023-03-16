<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command\CleanCategoryEnrichedValuesByTemplateUuid;

use Akeneo\Category\Application\Enrichment\CategoryDataCleaner;
use Akeneo\Category\Application\Query\GetEnrichedValuesByTemplateUuid;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CleanCategoryEnrichedValuesByTemplateUuidCommandHandler
{
    private const CATEGORY_BATCH_SIZE = 100;

    public function __construct(
        private readonly GetEnrichedValuesByTemplateUuid $getEnrichedValuesByTemplateUuid,
        private readonly CategoryDataCleaner $categoryDataCleaner,
    ) {
    }

    public function __invoke(CleanCategoryEnrichedValuesByTemplateUuidCommand $command): void
    {
        foreach($this->getEnrichedValuesByTemplateUuid->byBatchesOf(
            TemplateUuid::fromString($command->templateUuid),
            self::CATEGORY_BATCH_SIZE
        ) as $valuesByCode) {
            $this->categoryDataCleaner->cleanByTemplateUuid($valuesByCode, $command->templateUuid);
        }
    }
}
