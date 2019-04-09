<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query;

use Akeneo\Pim\Enrichment\Component\Category\Query\GetViewableCategoryCodesInterface;
use Doctrine\DBAL\Connection;

/**
 * Given a list of category codes, get viewable category codes
 *
 * @author    Anaël CHARDAN <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetViewableCategoryCodes implements GetViewableCategoryCodesInterface
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
    public function getViewableCategoryCodes(int $userId, array $categoryCodes): array
    {
        $query = <<<SQL
SELECT category.code as category_code
FROM pim_catalog_category category
WHERE category.code IN (?)
SQL;

        $results = $this->connection->fetchAll(
            $query,
            [$categoryCodes],
            [Connection::PARAM_STR_ARRAY]
        );

        return array_map(function ($result) {
            return $result['category_code'];
        }, $results);
    }
}
