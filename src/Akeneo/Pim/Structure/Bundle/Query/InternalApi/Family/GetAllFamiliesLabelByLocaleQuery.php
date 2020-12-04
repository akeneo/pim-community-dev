<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\InternalApi\Family;

use Akeneo\Pim\Structure\Component\Query\InternalApi\GetAllFamiliesLabelByLocaleQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetAllFamiliesLabelByLocaleQuery implements GetAllFamiliesLabelByLocaleQueryInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function execute(string $localeCode): array
    {
        $query = <<<SQL
SELECT 
   family.code,
   COALESCE(NULLIF(ft.label, ''), CONCAT('[', family.code, ']')) as label
FROM pim_catalog_family family
LEFT JOIN pim_catalog_family_translation ft ON family.id = ft.foreign_key AND ft.locale = :locale
ORDER BY label
SQL;

        $families = $this->connection->executeQuery($query, ['locale' => $localeCode])->fetchAll(\PDO::FETCH_ASSOC);

        $result = [];
        foreach ($families as $family) {
            $result[$family['code']] = $family['label'];
        }

        return $result;
    }
}
