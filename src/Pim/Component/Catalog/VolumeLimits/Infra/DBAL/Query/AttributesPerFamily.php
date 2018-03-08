<?php declare(strict_types=1);

namespace Pim\Component\Catalog\VolumeLimits\Infra\DBAL\Query;

use Doctrine\DBAL\Connection;
use Pim\Component\Catalog\VolumeLimits\Model\Query as Model;

final class AttributesPerFamily implements Model\AttributesPerFamily
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function __invoke(): array
    {
        return $this->connection->query(<<<SQL
            select avg(a.rcount) mean, max(a.rcount) max
            from (select count(attribute_id) rcount from pim_catalog_family_attribute group by family_id) a
SQL
        )->fetch();
    }
}
