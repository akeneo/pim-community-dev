<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid;

use Akeneo\Pim\Enrichment\Bundle\Command\MigrateToUuid\Utils\StatusAwareTrait;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class MigrateToUuidAddConstraints implements MigrateToUuidStep
{
    use MigrateToUuidTrait;
    use StatusAwareTrait;

    private array $constraintsToAdd = [
        [
            'tableName' => 'pim_catalog_association',
            'constraintName' => 'product_foreign_key_idx',
            'query' => 'ALTER TABLE pim_catalog_association ADD CONSTRAINT product_foreign_key_idx UNIQUE (owner_uuid, association_type_id)',
        ],
    ];

    public function __construct(private Connection $connection, private LoggerInterface $logger)
    {
    }

    public function getDescription(): string
    {
        return 'Add constraints on uuid foreign columns';
    }

    public function getName(): string
    {
        return 'add_constraints_on_uuid_columns';
    }

    public function shouldBeExecuted(): bool
    {
        return 0 < $this->getMissingCount();
    }

    public function getMissingCount(): int
    {
        $count = 0;
        foreach ($this->constraintsToAdd as $constraint) {
            if (!$this->constraintExists($constraint['tableName'], $constraint['constraintName'])) {
                $count++;
            }
        }

        return $count;
    }

    public function addMissing(Context $context): bool
    {
        $logContext = $context->logContext;
        $updatedItems = 0;

        foreach ($this->constraintsToAdd as $constraint) {
            $logContext->addContext('substep', $constraint['tableName']);

            if (!$this->constraintExists($constraint['tableName'], $constraint['constraintName'])) {
                $this->logger->notice(sprintf('Will add %s constraint', $constraint['constraintName']), $logContext->toArray());
                if (!$context->dryRun()) {
                    $this->connection->executeQuery($constraint['query']);
                    $this->logger->notice('Substep done', $logContext->toArray(['updated_items_count' => $updatedItems+=1]));
                }
            }
        }

        return true;
    }
}
