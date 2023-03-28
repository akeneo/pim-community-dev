<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Job;

use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogIdsUsingAttributesInProductMappingQueryInterface;
use Akeneo\Catalogs\Application\Service\DispatchAttributeRemovedEventInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

class NotifyOnAttributeRemovalTasklet implements TaskletInterface
{
    private ?StepExecution $stepExecution = null;

    public function __construct(
        private readonly GetCatalogIdsUsingAttributesInProductMappingQueryInterface $getCatalogIdsUsingAttributesInProductMappingQuery,
        private readonly DispatchAttributeRemovedEventInterface $dispatchAttributeRemovedEvent,
    ) {
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute(): void
    {
        if (null === $this->stepExecution) {
            throw new \LogicException('The variable $stepExecution should not be null.');
        }

        /** @var string[] $attributeCodes */
        $attributeCodes = $this->stepExecution->getJobParameters()->get('attribute_codes');

        $catalogsIds = $this->getCatalogIdsUsingAttributesInProductMappingQuery->execute($attributeCodes);

        foreach ($catalogsIds as $catalogId) {
            ($this->dispatchAttributeRemovedEvent)($catalogId);
        }
    }
}
