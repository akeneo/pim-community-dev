<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Query;

use Akeneo\Component\Versioning\Model\Version;
use Doctrine\ORM\EntityManagerInterface;
use Pim\Component\Catalog\Model\Product;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Query that converts a product to a variant product
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConvertProductToVariantProduct
{
    private const PRODUCT_VARIANT_TYPE = 'variant_product';

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
     * {@inheritdoc}
     */
    public function execute(ProductInterface $variantProduct): void
    {
        $sql = <<<SQL
UPDATE pim_catalog_product AS variant_product
SET variant_product.product_type = :product_type
WHERE (id = :id)
SQL;

        $this->entityManager->getConnection()->executeQuery($sql, [
            'product_type' => self::PRODUCT_VARIANT_TYPE,
            'id' => $variantProduct->getId()
        ]);

        $queryBuilder = $this->entityManager->createQueryBuilder();
        $query = $queryBuilder->update(Version::class, 'version')
            ->set(
                'version.resourceName',
                    $queryBuilder->expr()->literal(Product::class)
            )
            ->where('version.resourceId = :resource_id')
            ->setParameter('resource_id', $variantProduct->getId())
            ->getQuery();

        $query->execute();
    }
}
