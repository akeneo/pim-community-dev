<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Query;

use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Find all sort orders equals or superior to the given attribute group sort order.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FindAttributeGroupOrdersEqualOrSuperiorTo
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param AttributeGroup $attributeGroup
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return string[]
     */
    public function execute(AttributeGroup $attributeGroup): array
    {
        $sql = <<<SQL
        SELECT DISTINCT(ag.sort_order)
        FROM pim_catalog_attribute_group ag
        WHERE (ag.sort_order >= :attribute_group_order)
        AND ag.code != :attribute_group_code
        ORDER BY ag.sort_order ASC
SQL;
        $query = $this->entityManager->getConnection()->executeQuery(
            $sql,
            [
                'attribute_group_code' => $attributeGroup->getCode(),
                'attribute_group_order' => $attributeGroup->getSortOrder(),
            ]
        );

        $results = $query->fetchAll(\PDO::FETCH_COLUMN);

        return $results;
    }
}
