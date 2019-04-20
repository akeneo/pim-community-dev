<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\AttributeOption\Sql;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionCodes as GetExistingAttributeOptionCodesInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetExistingAttributeOptionCodes implements GetExistingAttributeOptionCodesInterface
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fromOptionCodes(array $optionCodes): array
    {
        if (empty($optionCodes)) {
            return [];
        }

        $optionCodes = (function (... $optionCodes): array {
            return $optionCodes;
        })(...$optionCodes);

        $query = <<<SQL
        SELECT code
        FROM pim_catalog_attribute_option
        WHERE code IN (:optionCodes)
SQL;

        return $this->connection->executeQuery(
            $query,
            ['optionCodes' => $optionCodes],
            ['optionCodes' => Connection::PARAM_STR_ARRAY]
        )->fetchAll(FetchMode::COLUMN);
    }
}
