<?php

namespace Akeneo\Tool\Bundle\ElasticsearchBundle\Domain\Model;

/**
 * This interface represents an Elasticsearch projection of a document which should be migrated from its
 * former document_id to a new one.
 * The Elasticsearch client will catch these flagged projections to remove the document with the former document_id
 * before inserting the new projection with the new document_id.
 *
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AffectedByMigrationProjection
{
    public function shouldBeMigrated(): bool;

    public function getFormerDocumentId(): string;

    public function toArray(): array;
}
