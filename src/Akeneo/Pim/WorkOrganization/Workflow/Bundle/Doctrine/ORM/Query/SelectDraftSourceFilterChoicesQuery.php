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

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Doctrine\ORM\Query;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\SelectDraftSourceFilterChoicesQueryInterface;
use Doctrine\DBAL\Connection;

class SelectDraftSourceFilterChoicesQuery implements SelectDraftSourceFilterChoicesQueryInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(): array
    {
        $sql = <<<SQL
(
    SELECT DISTINCT source, source_label
    FROM pimee_workflow_product_model_draft
)
UNION
(
    SELECT DISTINCT source, source_label
    FROM pimee_workflow_product_draft
)
SQL;
        $stmt = $this->connection->executeQuery($sql);

        $sources = [];
        foreach ($stmt->fetchAll() as $result) {
            $sources[$result['source_label']] = $result['source'];
        }

        return $sources;
    }
}
