<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command\CleanCategoryTemplateAndEnrichedValues;

use Akeneo\Category\Application\Enrichment\CategoryAttributeValuesCleaner;
use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Domain\Query\DeleteCategoryTreeTemplateByTemplateUuid;
use Akeneo\Category\Domain\Query\GetEnrichedValuesByTemplateUuid;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CleanCategoryTemplateAndEnrichedValuesCommandHandler
{
    private const CATEGORY_BATCH_SIZE = 100;

    public function __construct(
        private readonly GetEnrichedValuesByTemplateUuid $getEnrichedValuesByTemplateUuid,
        private readonly CategoryAttributeValuesCleaner $categoryDataCleaner,
        private readonly GetAttribute $getCategoryTemplateAttributes,
        private readonly DeleteCategoryTreeTemplateByTemplateUuid $deleteCategoryTreeTemplateByTemplateUuid,
    ) {
    }

    public function __invoke(CleanCategoryTemplateAndEnrichedValuesCommand $command): void
    {
        $templateUuid = TemplateUuid::fromString($command->templateUuid);
        $templateAttributes = $this->getCategoryTemplateAttributes
            ->byTemplateUuid($templateUuid)
            ->getAttributes();
        foreach ($this->getEnrichedValuesByTemplateUuid->byBatchesOf(
            $templateUuid,
            self::CATEGORY_BATCH_SIZE,
        ) as $valuesByCode) {
            $this->categoryDataCleaner->cleanByTemplateAttributesUuid($valuesByCode, $templateAttributes);
        }

        ($this->deleteCategoryTreeTemplateByTemplateUuid)($templateUuid);
    }
}
