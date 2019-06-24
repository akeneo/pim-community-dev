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

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
final class CreateTableAttributeCreatedQuery
{
    const QUERY = <<<'SQL'
CREATE TABLE IF NOT EXISTS pimee_franklin_insights_attribute_created(
    attribute_code VARCHAR(100) NOT NULL,
    attribute_type VARCHAR(255) NOT NULL,
    created DATETIME NOT NULL COMMENT '(DC2Type:datetime)' DEFAULT CURRENT_TIMESTAMP, 
    INDEX IDX_FI_AATF_attribute_code (attribute_code)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB ROW_FORMAT = DYNAMIC
SQL;
}
