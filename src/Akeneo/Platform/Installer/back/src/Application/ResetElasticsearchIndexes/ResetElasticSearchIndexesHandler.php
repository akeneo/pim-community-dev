<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\Installer\Application\ResetElasticsearchIndexes;

use Akeneo\Platform\Installer\Domain\Query\Elasticsearch\ResetIndexesInterface;

final class ResetElasticSearchIndexesHandler
{
    public function __construct(
        private readonly ResetIndexesInterface $resetIndexes
    ) {}
    public function handle(ResetElasticSearchIndexesCommand $elasticSearchIndexesCommand): void
    {
        $elasticSearchIndexesCommand->getIo()->info('Reset elasticsearch indexes');
        $this->resetIndexes->reset();
    }
}
