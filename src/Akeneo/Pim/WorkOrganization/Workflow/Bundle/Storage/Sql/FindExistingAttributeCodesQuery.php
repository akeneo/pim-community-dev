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

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Storage\Sql;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\FindExistingAttributeCodesQuery as QueryInterface;
use Akeneo\Tool\Bundle\StorageUtilsBundle\Doctrine\TableNameBuilder;
use Doctrine\DBAL\Connection;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class FindExistingAttributeCodesQuery implements QueryInterface
{
    /** @var Connection */
    public $connection;

    public function __construct(
        Connection $connection
    ) {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(array $attributeCodes): array
    {
        $sql = <<<SQL
SELECT attribute.code
FROM pim_catalog_attribute as attribute
WHERE attribute.code IN (:attribute_codes)
SQL;

        $statement = $this->connection->executeQuery(
            $sql,
            ['attribute_codes' => $attributeCodes],
            ['attribute_codes' => Connection::PARAM_STR_ARRAY]
        );

        return $statement->fetchAll(\PDO::FETCH_COLUMN);
    }
}
