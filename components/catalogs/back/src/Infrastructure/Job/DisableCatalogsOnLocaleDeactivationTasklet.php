<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Job;

use Akeneo\Catalogs\Application\Persistence\Catalog\DisableCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogIdsUsingLocalesAsFilterQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogIdsUsingLocalesInMappingQueryInterface;
use Akeneo\Catalogs\Application\Service\DispatchInvalidCatalogDisabledEventInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

class DisableCatalogsOnLocaleDeactivationTasklet implements TaskletInterface
{
    private ?StepExecution $stepExecution = null;

    public function __construct(
        private readonly GetCatalogIdsUsingLocalesAsFilterQueryInterface $getCatalogIdsUsingLocalesAsFilterQuery,
        private readonly GetCatalogIdsUsingLocalesInMappingQueryInterface $getCatalogIdsUsingLocalesInMappingQuery,
        private readonly DisableCatalogQueryInterface $disableCatalogsQuery,
        private readonly DispatchInvalidCatalogDisabledEventInterface $dispatchInvalidCatalogDisabledEvent,
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

        /** @var string[] $localeCodes */
        $localeCodes = $this->stepExecution->getJobParameters()->get('locale_codes');

        $catalogsIds = \array_unique(
            \array_merge(
                $this->getCatalogIdsUsingLocalesAsFilterQuery->execute($localeCodes),
                $this->getCatalogIdsUsingLocalesInMappingQuery->execute($localeCodes)
            )
        );

        foreach ($catalogsIds as $catalogId) {
            $this->disableCatalogsQuery->execute($catalogId);
            ($this->dispatchInvalidCatalogDisabledEvent)($catalogId);
        }
    }
}
