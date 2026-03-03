<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\Attribute\Sql;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\GetMainIdentifierAttributeCode;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetMainIdentifierAttributeCode implements GetMainIdentifierAttributeCode
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    public function __invoke(): string
    {
        return $this->connection->executeQuery(<<<SQL
            SELECT code FROM pim_catalog_attribute WHERE main_identifier = TRUE LIMIT 1
        SQL)->fetchOne();
    }
}
