<?php

declare(strict_types=1);


namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\DBAL\EventListener;

use Doctrine\DBAL\Event\SchemaIndexDefinitionEventArgs;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * This listener prevents the SchemaManager to try to instantiate the idx_identifiers index during database
 * introspection on the pim_catalog_product_identifiers table, because it does not involve any column,
 * being a multi-valued index. Because of a probable bug in \Doctrine\DBAL\Schema\AbstractSchemaManager::_getPortableTableIndexesList,
 * the instantiation of such an index causes an error and throws an exception
 *
 * This index is created manually, and doctrine does not need to know about it since it's neither a foreign key
 * nor a primary key index
 *
 * For the record, the database introspection occurs when
 *  - calling `doctrine:schema:update` or `doctrine:schema:drop` commands
 *  - using the $schema variable in any doctrine migration class
 *
 * @see https://dev.mysql.com/doc/refman/8.0/en/create-index.html#create-index-multi-valued
 * @see \Doctrine\DBAL\Schema\AbstractSchemaManager::_getPortableTableIndexesList
 */
final class SkipMultiValuedIndexDuringIntrospectionListener
{
    private const TABLE_NAME = 'pim_catalog_product_identifiers';
    private const INDEX_NAME = 'idx_identifiers';

    public function onSchemaIndexDefinition(SchemaIndexDefinitionEventArgs $event): void
    {
        if (self::TABLE_NAME === $event->getTable() && self::INDEX_NAME === ($event->getTableIndex()['name'] ?? null)) {
            $event->preventDefault();
        }
    }
}
