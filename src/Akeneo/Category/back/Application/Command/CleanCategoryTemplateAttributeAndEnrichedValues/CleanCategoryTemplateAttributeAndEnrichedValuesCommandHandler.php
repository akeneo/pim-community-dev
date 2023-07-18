<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command\CleanCategoryTemplateAttributeAndEnrichedValues;

use Akeneo\Category\Application\Enrichment\CategoryAttributeValuesCleaner;
use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Application\Query\GetDeactivatedAttribute;
use Akeneo\Category\Domain\Query\DeleteTemplateAttribute;
use Akeneo\Category\Domain\Query\GetEnrichedValuesByTemplateUuid;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CleanCategoryTemplateAttributeAndEnrichedValuesCommandHandler
{
    private const CATEGORY_BATCH_SIZE = 100;

    public function __construct(
        private readonly GetEnrichedValuesByTemplateUuid $getEnrichedValuesByTemplateUuid,
        private readonly CategoryAttributeValuesCleaner  $categoryDataCleaner,
        private readonly GetDeactivatedAttribute         $getDeactivatedCategoryTemplateAttributes,
        private readonly DeleteTemplateAttribute         $deleteTemplateAttribute,
    ) {
    }

    public function __invoke(CleanCategoryTemplateAttributeAndEnrichedValuesCommand $command): void
    {
        $templateUuid = TemplateUuid::fromString($command->templateUuid);
        $attributeUuid = AttributeUuid::fromString($command->attributeUuid);
        $templateAttributes = $this->getDeactivatedCategoryTemplateAttributes
            ->byUuids([$attributeUuid])
            ->getAttributes();
        foreach ($this->getEnrichedValuesByTemplateUuid->byBatchesOf(
            $templateUuid,
            self::CATEGORY_BATCH_SIZE,
        ) as $valuesByCode) {
            $this->categoryDataCleaner->cleanByTemplateAttributesUuid($valuesByCode, $templateAttributes);
        }

        ($this->deleteTemplateAttribute)($templateUuid, $attributeUuid);
    }
}
