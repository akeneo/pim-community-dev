<?php

declare(strict_types=1);

namespace Pim\Bundle\DataGridBundle\Query\Sql;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Pim\Bundle\DataGridBundle\Query\ListAttributesQuery;

/**
 * List the attributes useable as filters or columns in the product grid.
 *
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributesUseableInProductGrid implements ListAttributesQuery
{
    /** @var Connection */
    private $connection;

    /** @var int */
    private $attributesPerPage;

    /**
     * @param Connection $connection
     * @param int        $attributesPerPage
     */
    public function __construct(Connection $connection, int $attributesPerPage)
    {
        $this->connection = $connection;
        $this->attributesPerPage = $attributesPerPage;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(string $locale, int $page, string $searchOnLabel = ''): array
    {
        $page = max($page, 1);
        $offset = ($page - 1) * $this->attributesPerPage;
        $limit = $this->attributesPerPage;

        $sql = <<<SQL
SELECT DISTINCT(att.code) AS code,
  att.attribute_type AS type, att.sort_order AS `order`, att.metric_family AS metricFamily, g.sort_order AS groupOrder,
  COALESCE(att_trans.label, CONCAT('[', att.code, ']')) AS label,
  COALESCE(group_trans.label, CONCAT('[', g.code, ']')) AS `group`
FROM pim_catalog_attribute AS att
LEFT JOIN pim_catalog_attribute_group AS g ON att.group_id = g.id
LEFT JOIN pim_catalog_attribute_translation AS att_trans ON att.id = att_trans.foreign_key AND att_trans.locale = :locale
LEFT JOIN pim_catalog_attribute_group_translation AS group_trans ON g.id = group_trans.foreign_key AND group_trans.locale = :locale
WHERE att.useable_as_grid_filter = 1
HAVING label LIKE :search
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
