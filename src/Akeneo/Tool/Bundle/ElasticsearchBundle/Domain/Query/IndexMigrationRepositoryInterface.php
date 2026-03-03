<?php

declare(strict_types=1);

/*
 * @copyright 2021 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Tool\Bundle\ElasticsearchBundle\Domain\Query;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Domain\Model\IndexMigration;

interface IndexMigrationRepositoryInterface
{
    public function save(IndexMigration $indexMigration): void;
}
