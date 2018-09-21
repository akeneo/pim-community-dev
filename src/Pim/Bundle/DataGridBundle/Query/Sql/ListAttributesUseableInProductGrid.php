<?php

declare(strict_types=1);

namespace Pim\Bundle\DataGridBundle\Query\Sql;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Pim\Bundle\DataGridBundle\Query\ListAttributesUseableInProductGrid as ListAttributesUseableInProductGridQuery;

/**
 * List the attributes useable as filters or columns in the product grid.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ListAttributesUseableInProductGrid implements ListAttributesUseableInProductGridQuery
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
    public function fetch(string $locale, int $page, string $searchOnLabel = '', int $userId = null): array
    {
        $page = max($page, 1);
        $offset = ($page - 1) * ListAttributesUseableInProductGridQuery::ATTRIBUTES_PER_PAGE;
        $limit = ListAttributesUseableInProductGridQuery::ATTRIBUTES_PER_PAGE;

        $sql = <<<SQL
SELECT DISTINCT 
  att.code, att.attribute_type AS type, att.sort_order AS `order`, att.metric_family AS metricFamily, g.sort_order AS groupOrder,
  COALESCE(att_trans.label, CONCAT('[', att.code, ']')) AS label,
  COALESCE(group_trans.label, CONCAT('[', g.code, ']')) AS `group`
FROM pim_catalog_attribute AS att
INNER JOIN pim_catalog_attribute_group AS g ON att.group_id = g.id
LEFT JOIN pim_catalog_attribute_translation AS att_trans ON att.id = att_trans.foreign_key AND att_trans.locale = :locale
LEFT JOIN pim_catalog_attribute_group_translation AS group_trans ON g.id = group_trans.foreign_key AND group_trans.locale = :locale
WHERE att.useable_as_grid_filter = 1 AND COALESCE(att_trans.label, att.code) LIKE :search
ORDER BY g.sort_order ASC, att.sort_order ASC
LIMIT $limit OFFSET $offset
SQL;

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('locale', $locale, Type::STRING);
        $stmt->bindValue('search', "%$searchOnLabel%", Type::STRING);

        $stmt->execute();
        $attributes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $attributes = array_map(function ($attribute) {
            $attribute['order'] = (int) $attribute['order'];
            $attribute['groupOrder'] = (int) $attribute['groupOrder'];

            return $attribute;
        }, $attributes);

        return $attributes;
    }
}
