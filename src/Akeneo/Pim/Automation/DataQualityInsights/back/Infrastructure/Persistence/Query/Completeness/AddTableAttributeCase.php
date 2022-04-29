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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Completeness;

final class AddTableAttributeCase implements AttributeCase
{
    public function getCase(): string
    {
        return "
                WHEN attribute.attribute_type = 'pim_catalog_table'
                    THEN CONCAT(
                            attribute.code,
                            '-',
                            (
                                SELECT GROUP_CONCAT(table_column.id ORDER BY table_column.id SEPARATOR '-')
                                FROM pim_catalog_table_column table_column
                                WHERE (table_column.is_required_for_completeness IS NULL OR table_column.is_required_for_completeness IS FALSE)
                                AND table_column.attribute_id = attribute.id
                            )
                        )
                ";
    }
}
