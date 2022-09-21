<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Job;

use Akeneo\Catalogs\Application\Persistence\DisableCatalogsQueryInterface;
use Akeneo\Catalogs\Application\Persistence\GetCatalogsToDisableOnAttributeOptionRemovalQueryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

class DisableCatalogsOnAttributeOptionRemovalTasklet implements TaskletInterface
{
    private ?StepExecution $stepExecution = null;

    public function __construct(
        private GetCatalogsToDisableOnAttributeOptionRemovalQueryInterface $getCatalogsToDisableQuery,
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

        /** @var string $attributeCode */
        $attributeCode = $this->stepExecution->getJobParameters()->get('attribute_code');

        /** @var string $attributeOptionCode */
        $attributeOptionCode = $this->stepExecution->getJobParameters()->get('attribute_option_code');

        $catalogsUUID = $this->getCatalogsToDisableQuery->execute($attributeCode, $attributeOptionCode);
        $this->disableCatalogsQuery->execute($catalogsUUID);
    }
}
