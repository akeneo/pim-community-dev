<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityManager;
use Pim\Component\Catalog\Repository\CompletenessRepositoryInterface;

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
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var string
     */
    protected $productClass;

    /**
     * @param EntityManager $entityManager
     * @param string        $productClass
     */
    public function __construct(
        EntityManager $entityManager,
        $productClass
    ) {
        $this->entityManager = $entityManager;
        $this->productClass = $productClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductsCountPerChannels($localeCode)
    {
        $sql = <<<SQL
SELECT t.label, COUNT(DISTINCT p.id) as total FROM pim_catalog_channel ch
    JOIN pim_catalog_channel_translation t ON t.foreign_key = ch.id
    JOIN %category_table% ca ON ca.root = ch.category_id
    JOIN %category_join_table% cp ON cp.category_id = ca.id
    JOIN %product_table% p ON p.id = cp.product_id
    WHERE p.is_enabled = 1
    AND t.locale = '%locale%'
    GROUP BY ch.id, t.label
SQL;

        $sql = $this->applyTableNames($sql);
        $sql = $this->applyParameters($sql, $localeCode);

        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * {@inheritdoc}
     */
    public function getCompleteProductsCountPerChannels($localeCode)
    {
        $sql = <<<SQL
    SELECT t.label, lo.code as locale, COUNT(DISTINCT co.product_id) as total FROM pim_catalog_channel ch
    JOIN pim_catalog_channel_translation t ON t.foreign_key = ch.id
    JOIN %category_table% ca ON ca.root = ch.category_id
    JOIN %category_join_table% cp ON cp.category_id = ca.id
    JOIN %product_table% p ON p.id = cp.product_id
    JOIN pim_catalog_channel_locale cl ON cl.channel_id = ch.id
    JOIN pim_catalog_locale lo ON lo.id = cl.locale_id
    LEFT JOIN pim_catalog_completeness co
        ON co.locale_id = lo.id AND co.channel_id = ch.id AND co.product_id = p.id AND co.ratio = 100
    WHERE p.is_enabled = 1
    AND t.locale = '%locale%'
    GROUP BY ch.id, lo.id, t.label, lo.code
SQL;
        $sql = $this->applyTableNames($sql);
        $sql = $this->applyParameters($sql, $localeCode);

        $stmt = $this->entityManager->getConnection()->prepare($sql);
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
        $categoryMapping = $this->entityManager
            ->getClassMetadata($this->productClass)
            ->getAssociationMapping('categories');

        $categoryMetadata = $this->entityManager->getClassMetadata($categoryMapping['targetEntity']);

        return strtr(
            $sql,
            [
                '%category_table%'      => $categoryMetadata->getTableName(),
                '%category_join_table%' => $categoryMapping['joinTable']['name'],
                '%product_table%'       => $this->entityManager->getClassMetadata($this->productClass)->getTableName(),
            ]
        );
    }

    /**
     * Replace parameters placeholders by their real values
     *
     * @param string $sql
     * @param string $localeCode
     *
     * @return string
     */
    protected function applyParameters($sql, $localeCode)
    {
        return strtr($sql, ['%locale%' => $localeCode]);
    }
}
