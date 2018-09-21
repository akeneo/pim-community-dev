<?php

declare(strict_types=1);

namespace Pim\Bundle\DataGridBundle\Query\Sql;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Pim\Bundle\DataGridBundle\Query\ListAttributesUseableAsColumnInProductGrid as ListAttributesUseableAsColumnInProductGridQuery;


/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ListAttributesUseableAsColumnInProductGrid implements ListAttributesUseableAsColumnInProductGridQuery
{
    /** @var Connection */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(string $locale, int $userId = null): array
    {
        $sql = <<<SQL
SELECT DISTINCT att.code, att.sort_order AS attribute_order, g.sort_order AS group_order,
  COALESCE(att_trans.label, CONCAT('[', att.code, ']')) AS label
FROM pim_catalog_attribute AS att
INNER JOIN pim_catalog_attribute_group AS g ON att.group_id = g.id
LEFT JOIN pim_catalog_attribute_translation AS att_trans ON att.id = att_trans.foreign_key AND att_trans.locale = :locale
WHERE att.useable_as_grid_filter = 1
ORDER BY group_order ASC, attribute_order ASC
SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('locale', $locale, Type::STRING);
        $stmt->execute();

        $results = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $attributes = [];
        foreach ($results as $resultRow) {
            $attributes[$resultRow['code']] = [
                'code'  => $resultRow['code'],
                'label' => $resultRow['label']
            ];
        }

        return $attributes;
    }
}
