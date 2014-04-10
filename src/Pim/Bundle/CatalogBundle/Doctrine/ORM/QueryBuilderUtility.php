<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM;

use Doctrine\ORM\QueryBuilder;

/**
 * Provides util methods to ease the query building
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class QueryBuilderUtility
{
    /**
     * We update the query to count, get ids and fetch data, so, we can lost expected query builder parameters,
     * and we have to remove them
     *
     * @param QueryBuilder $qb
     */
    public static function removeExtraParameters(QueryBuilder $qb)
    {
        $parameters    = $qb->getParameters();
        $dql           = $qb->getDQL();
        foreach ($parameters as $parameter) {
            if (strpos($dql, ':'.$parameter->getName()) === false) {
                $parameters->removeElement($parameter);
            }
        }
        $qb->setParameters($parameters);
    }

    /**
     * Replaces name of tables in DBAL queries
     *
     * @param EntityManager $em
     * @param string        $entityName
     * @param string        $sql
     *
     * @return string
     */
    public static function prepareDBALQuery($em, $entityName, $sql)
    {
        $productMetadata = $em->getClassMetadata($entityName);

        $categoryMapping = $productMetadata->getAssociationMapping('categories');
        $familyMapping   = $productMetadata->getAssociationMapping('family');
        $valueMapping    = $productMetadata->getAssociationMapping('values');

        $valueMetadata = $em->getClassMetadata($valueMapping['targetEntity']);

        $attributeMapping  = $valueMetadata->getAssociationMapping('attribute');
        $attributeMetadata = $em->getClassMetadata($attributeMapping['targetEntity']);

        $familyMetadata = $em->getClassMetadata($familyMapping['targetEntity']);

        $attributeFamMapping = $familyMetadata->getAssociationMapping('attributes');

        return strtr(
            $sql,
            [
                '%category_join_table%'    => $categoryMapping['joinTable']['name'],
                '%product_table%'          => $productMetadata->getTableName(),
                '%product_value_table%'    => $valueMetadata->getTableName(),
                '%attribute_table%'        => $attributeMetadata->getTableName(),
                '%family_table%'           => $familyMetadata->getTableName(),
                '%family_attribute_table%' => $attributeFamMapping['joinTable']['name']
            ]
        );
    }
}
