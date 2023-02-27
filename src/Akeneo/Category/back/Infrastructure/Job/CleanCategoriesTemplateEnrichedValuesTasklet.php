<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Job;

use Akeneo\Category\Application\Enrichment\CategoryDataCleaner;
use Akeneo\Category\Application\Enrichment\Filter\AttributesFilter;
use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Domain\Model\Attribute\Attribute;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CleanCategoriesTemplateEnrichedValuesTasklet implements TaskletInterface
{
    private StepExecution $stepExecution;

    public function __construct(
        private readonly CategoryDataCleaner $categoryDataCleaner,
        private GetAttribute $getAttribute,
    ) {
    }

    public function setStepExecution(StepExecution $stepExecution): self
    {
        $this->stepExecution = $stepExecution;

        return $this;
    }

    public function execute(): void
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $templateUuid = $jobParameters->get('template_uuid');

        $attributeCollection = $this->getAttribute->byTemplateUuid(TemplateUuid::fromString($templateUuid));

        $attributeUuidList = [];
        foreach ($attributeCollection as $attribute) {
            /* @var Attribute $attribute */
            $attributeUuidList[] = (string)$attribute->getUuid();
        }

        ($this->categoryDataCleaner)(
            [
                'attribute_list' => $attributeUuidList,
            ],
            new AttributesFilter(),
        );
    }
}
