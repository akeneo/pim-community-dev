<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Job;

use Akeneo\Catalogs\Application\Persistence\DisableCatalogsQueryInterface;
use Akeneo\Catalogs\Application\Persistence\GetCatalogIdsContainingCategoryQueryInterface;
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

        /** @var string $categoryCode */
        $categoryCode = $this->stepExecution->getJobParameters()->get('category_code');

        $catalogsIds = $this->getCatalogsToDisableQuery->execute($categoryCode);
        $this->disableCatalogsQuery->execute($catalogsIds);
    }
}
