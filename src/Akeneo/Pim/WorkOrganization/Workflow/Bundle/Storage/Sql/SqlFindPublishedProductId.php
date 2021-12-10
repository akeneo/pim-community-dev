<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Storage\Sql;

use Akeneo\Pim\Enrichment\Component\Product\Query\FindId;
use Doctrine\DBAL\Connection;

final class SqlFindPublishedProductId implements FindId
{
    public function __construct(private Connection $connection)
    {
    }

    public function fromIdentifier(string $identifier): null|string
    {
        $id = $this->connection->executeQuery(
            'SELECT id FROM pimee_workflow_published_product WHERE identifier = :identifier',
            ['identifier' => $identifier]
        )->fetchOne();

        return false === $id ? null : (string)$id;
    }
}
