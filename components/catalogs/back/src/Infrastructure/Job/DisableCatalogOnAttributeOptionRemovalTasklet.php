<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Job;

use Akeneo\Catalogs\Application\Persistence\GetEnabledCatalogsByAttributeCodeAndAttributeOptionCodeQueryInterface;
use Akeneo\Catalogs\Application\Persistence\UpsertCatalogQueryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

class DisableCatalogOnAttributeOptionRemovalTasklet implements TaskletInterface
{
    private StepExecution $stepExecution;

    public function __construct(
        private GetEnabledCatalogsByAttributeCodeAndAttributeOptionCodeQueryInterface $getEnabledCatalogsByAttributeCodeAndAttributeOptionCodeQuery,
        private UpsertCatalogQueryInterface $upsertCatalogQuery,
    ) {
    }

    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    public function execute()
    {
        $attributeCode = $this->stepExecution->getJobParameters()->get('attribute_code');
        $attributeOptionCode = $this->stepExecution->getJobParameters()->get('attribute_option_code');

        $catalogs = $this->getEnabledCatalogsByAttributeCodeAndAttributeOptionCodeQuery->execute($attributeCode, $attributeOptionCode);

        foreach ($catalogs as $catalog) {
            $this->upsertCatalogQuery->execute(
                $catalog->getId(),
                $catalog->getName(),
                $catalog->getOwnerUsername(),
                false,
            );
        }
    }
}
