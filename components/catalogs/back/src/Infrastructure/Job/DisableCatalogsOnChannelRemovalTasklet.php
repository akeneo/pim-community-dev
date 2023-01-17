<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Job;

use Akeneo\Catalogs\Application\Persistence\Catalog\DisableCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogIdsUsingChannelsAsFilterQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogIdsUsingChannelsInMappingQueryInterface;
use Akeneo\Catalogs\Application\Service\DispatchInvalidCatalogDisabledEventInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;

class DisableCatalogsOnChannelRemovalTasklet implements TaskletInterface
{
    private ?StepExecution $stepExecution = null;

    public function __construct(
        private readonly GetCatalogIdsUsingChannelsAsFilterQueryInterface $getCatalogIdsUsingChannelsAsFilterQuery,
        private readonly GetCatalogIdsUsingChannelsInMappingQueryInterface $getCatalogIdsUsingChannelsInMappingQuery,
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

        /** @var string[] $channelCodes */
        $channelCodes = $this->stepExecution->getJobParameters()->get('channel_codes');

        $catalogsIds = \array_unique(
            \array_merge(
                $this->getCatalogIdsUsingChannelsAsFilterQuery->execute($channelCodes),
                $this->getCatalogIdsUsingChannelsInMappingQuery->execute($channelCodes)
            )
        );

        foreach ($catalogsIds as $catalogId) {
            $this->disableCatalogsQuery->execute($catalogId);
            ($this->dispatchInvalidCatalogDisabledEvent)($catalogId);
        }
    }
}
