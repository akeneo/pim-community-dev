<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM;

use Pim\Bundle\CatalogBundle\Model\CompletenessRepositoryInterface;

/**
 * Completeness Repository for ORM
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessRepository implements CompletenessRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getProductsCountPerChannels()
    {
        $sql = <<<SQL
SELECT ch.label, COUNT(DISTINCT p.id) as total FROM pim_catalog_channel ch
    JOIN %category_table% ca ON ca.root = ch.category_id
    JOIN %category_join_table% cp ON cp.category_id = ca.id
    JOIN %product_table% p ON p.id = cp.product_id
    WHERE p.is_enabled = 1
    GROUP BY ch.id, ch.label
SQL;

        $sql = $this->applyTableNames($sql);

        $stmt = $this->doctrine->getConnection()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * {@inheritdoc}
     */
    public function getCompleteProductsCountPerChannels()
    {
        $sql = <<<SQL
    SELECT ch.label, lo.code as locale, COUNT(DISTINCT co.product_id) as total FROM pim_catalog_channel ch
    JOIN %category_table% ca ON ca.root = ch.category_id
    JOIN %category_join_table% cp ON cp.category_id = ca.id
    JOIN %product_table% p ON p.id = cp.product_id
    JOIN pim_catalog_channel_locale cl ON cl.channel_id = ch.id
    JOIN pim_catalog_locale lo ON lo.id = cl.locale_id
    LEFT JOIN pim_catalog_completeness co
        ON co.locale_id = lo.id AND co.channel_id = ch.id AND co.product_id = p.id AND co.ratio = 100
    WHERE p.is_enabled = 1
    GROUP BY ch.id, lo.id, ch.label, lo.code
SQL;
        $sql = $this->applyTableNames($sql);

        $stmt = $this->doctrine->getConnection()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Replace tables placeholders by their real name in the DB
     *
     * @param string $sql
     *
     * @return array
     */
    protected function applyTableNames($sql)
    {
        $categoryMapping = $this->getClassMetadata($this->productClass)->getAssociationMapping('categories');
        $categoryMetadata = $this->getClassMetadata($categoryMapping['targetEntity']);

        $valueMapping  = $this->getClassMetadata($this->productClass)->getAssociationMapping('values');
        $valueMetadata = $this->getClassMetadata($valueMapping['targetEntity']);

        $attributeMapping  = $valueMetadata->getAssociationMapping('attribute');
        $attributeMetadata = $this->getClassMetadata($attributeMapping['targetEntity']);

        return strtr(
            $sql,
            [
                '%category_table%'      => $categoryMetadata->getTableName(),
                '%category_join_table%' => $categoryMapping['joinTable']['name'],
                '%product_table%'       => $this->getClassMetadata($this->productClass)->getTableName(),
                '%product_value_table%' => $valueMetadata->getTableName(),
                '%attribute_table%'     => $attributeMetadata->getTableName()
            ]
        );
    }
}
