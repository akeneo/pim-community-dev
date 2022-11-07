<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Command;

use Akeneo\Platform\Bundle\InstallerBundle\Command\ZddMigration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\Types;
use Psr\Log\LoggerInterface;

class V20221005122309CleanProductDraftsWithReadOnlyAttributesZddMigration implements ZddMigration
{
    public function __construct(
        private Connection $connection,
        private LoggerInterface $logger,
    ) {
    }

    public function migrate(): void
    {
        $readOnlyAttributes = $this->getReadOnlyAttributes();
        if (empty($readOnlyAttributes)) {
            $this->logger->notice(
                sprintf('%s - end_migration - No read only attributes in database.', $this->getName())
            );
            return;
        }

        $executeMigration = function (array $draftBatch) use ($readOnlyAttributes) {
            foreach ($draftBatch as $draft) {
                $draftWithoutReadOnlyValues = $this->getDraftWithoutReadOnlyValues($draft, $readOnlyAttributes);

                if ($draftWithoutReadOnlyValues['raw_values'] !== $draft['raw_values']) {
                    $this->updateDraft($draftWithoutReadOnlyValues);
                }
            }
        };

        foreach ($this->getDrafts() as $draftBatch) {
            $this->connection->transactional(function () use ($executeMigration, $draftBatch) {
                $executeMigration($draftBatch);
            });
        }
    }

    private function getReadOnlyAttributes(): array
    {
        $readOnlyAttributeCodes = [];
        $formerId = 0;
        $sql = <<<SQL
           SELECT attribute.id, attribute.code, attribute.properties
           FROM pim_catalog_attribute as attribute
           WHERE attribute.id > :formerId 
       SQL;

        while (true) {
            $rows = $this->connection->fetchAllAssociative($sql, ['formerId' => $formerId]);
            if (empty($rows)) {
                return $readOnlyAttributeCodes;
            }
            $formerId = (int)end($rows)['id'];

            foreach ($rows as $row) {
                $properties = unserialize($row['properties'], ['allowed_classes' => true]);
                if (array_key_exists('is_read_only', $properties) && true === (bool) $properties['is_read_only']) {
                    $readOnlyAttributeCodes[] = $row['code'];
                }
            }
        }
    }

    private function getDraftWithoutReadOnlyValues(array $draft, array $readOnlyAttributeCodes): array
    {
        $rawValues = json_decode($draft['raw_values'], true, 512, JSON_THROW_ON_ERROR);
        $changes = json_decode($draft['changes'], true, 512, JSON_THROW_ON_ERROR);
        unset($draft['changes']);

        $readOnlyAttributeCodesIndexedByKey = array_flip($readOnlyAttributeCodes);
        $draft['raw_values'] = array_diff_ukey($rawValues, $readOnlyAttributeCodesIndexedByKey, 'strcasecmp');
        $draft['changes']['values'] = array_diff_ukey($changes['values'], $readOnlyAttributeCodesIndexedByKey, 'strcasecmp');
        $draft['changes']['review_statuses'] = array_diff_ukey($changes['review_statuses'], $readOnlyAttributeCodesIndexedByKey, 'strcasecmp');

        return $draft;
    }

    private function updateDraft(array $draft): void
    {
        $sql = <<<SQL
           UPDATE pimee_workflow_product_draft
           SET changes = :changes, raw_values = :raw_values
           WHERE id = :id
       SQL;

        $this->connection->executeQuery(
            $sql,
            ['id' => $draft['id'],'changes' => $draft['changes'], 'raw_values' => $draft['raw_values']],
            ['changes' => Types::JSON, 'raw_values' => Types::JSON]
        );
    }

    private function getDrafts(): iterable
    {
        $formerId = 0;

        $sql = <<<SQL
            SELECT draft.id, draft.changes, draft.raw_values
            FROM pimee_workflow_product_draft as draft
            WHERE draft.status IN (1,2) AND draft.id > :formerId
            ORDER BY draft.id
            LIMIT 1000
        SQL;

        while (true) {
            $rows = $this->connection->fetchAllAssociative($sql, ['formerId' => $formerId]);
            if (empty($rows)) {
                return;
            }
            $formerId = (int)end($rows)['id'];
            yield $rows;
        }
    }

    public function getName(): string
    {
        return 'CleanProductDraftsWithReadOnlyAttributes';
    }
}
