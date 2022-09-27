<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Job;

use Akeneo\Catalogs\Application\Persistence\Catalog\DisableCatalogsQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogIdsContainingCategoryQueryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

class DisableCatalogsOnCategoryRemovalTasklet implements TaskletInterface
{
    private ?StepExecution $stepExecution = null;

    public function __construct(
        private GetCatalogIdsContainingCategoryQueryInterface $getCatalogsToDisableQuery,
        private DisableCatalogsQueryInterface $disableCatalogsQuery,
    ) {
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute(): void
    {
        if (null === $this->stepExecution) {
            throw new \LogicException('the variable $stepExecution should not be null.');
        }

        /** @var string[] $categoryCodes */
        $categoryCodes = $this->stepExecution->getJobParameters()->get('category_codes');

        $catalogsIds = $this->getCatalogsToDisableQuery->execute($categoryCodes);
        $this->disableCatalogsQuery->execute($catalogsIds);
    }
}
