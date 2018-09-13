<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\EnrichedEntity\tests\back\Integration\Persistence\Helper;

use Doctrine\DBAL\Connection;

/**
 * This class is responsible for helping calling web routes.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class DatabaseHelper
{
    /** @var Connection */
    private $sqlConnection;

    public function __construct(Connection $sqlConnection)
    {
        $this->sqlConnection = $sqlConnection;
    }

    public function resetDatabase(): void
    {
        $resetQuery = <<<SQL
            DELETE FROM akeneo_enriched_entity_attribute;
            DELETE FROM akeneo_enriched_entity_record;
            DELETE FROM akeneo_enriched_entity_enriched_entity;
            DELETE FROM pim_catalog_attribute_group;
            DELETE FROM pim_catalog_attribute;
SQL;

        $this->sqlConnection->executeQuery($resetQuery);
    }

    public function resetCategoryChannelAndLocale()
    {
        $resetCategory = <<<SQL
INSERT INTO `pim_catalog_category` (`id`, `parent_id`, `code`, `created`, `root`, `lvl`, `lft`, `rgt`)
        VALUES
            (1, NULL, 'master', '2018-09-04 20:00:54', 1, 0, 1, 2);
SQL;
        $resetChannel = <<<SQL
        INSERT INTO `pim_catalog_channel` (`category_id`, `code`, `conversionUnits`)
        VALUES
            (1, 'mobile', 'a:0:{}'),
            (1, 'print', 'a:0:{}'),
            (1, 'ecommerce', 'a:0:{}');
SQL;

        $resetLocale = <<<SQL
        INSERT INTO `pim_catalog_locale` (`code`, `is_activated`)
        VALUES
            ('de_DE', 1),
            ('en_US', 1),
            ('fr_FR', 1);
SQL;
        $this->sqlConnection->executeQuery('DELETE FROM pim_catalog_locale;');
        $this->sqlConnection->executeQuery('DELETE FROM pim_catalog_channel;');
        $this->sqlConnection->executeQuery('DELETE FROM pim_catalog_category;');
        $this->sqlConnection->executeQuery($resetLocale);
        $this->sqlConnection->executeQuery($resetCategory);
        $this->sqlConnection->executeQuery($resetChannel);
    }
}
