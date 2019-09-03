<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Install\Query;

final class CreateTablePendingItemsQuery
{
    const QUERY = <<<'SQL'
CREATE TABLE IF NOT EXISTS pimee_franklin_insights_quality_highlights_pending_items
(
	entity_type varchar(20) not null,
	entity_id varchar(100) not null,
	action varchar(20) null,
	lock_id varchar(60) default '' not null,
	INDEX pending_items_action_index (action),
	INDEX pending_items_entity_type_index (entity_type),
	UNIQUE KEY(entity_type, entity_id, lock_id)
	
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC;

SQL;
}
