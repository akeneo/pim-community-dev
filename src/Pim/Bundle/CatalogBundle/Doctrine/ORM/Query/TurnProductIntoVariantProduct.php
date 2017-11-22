<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Query;

use Doctrine\ORM\EntityManagerInterface;
use Pim\Component\Catalog\EntityWithFamily\Query;
use Pim\Component\Catalog\Model\VariantProductInterface;

/**
 * Query that turns a product __invoke a variant product
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TurnProductIntoVariantProduct
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
    public function __invoke(VariantProductInterface $variantProduct): void
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

        $this->entityManager->getUnitOfWork()->registerManaged(
            $variantProduct,
            ['id' => $variantProduct->getId()],
            [
                'id' => $variantProduct->getId(),
                'parent' => null,
                'familyVariant' => null,
                'identifier' => $variantProduct->getIdentifier(),
                'groups' => $variantProduct->getGroups(),
                'associations' => $variantProduct->getAssociations(),
                'enabled' => $variantProduct->isEnabled(),
                'completenesses' => $variantProduct->getCompletenesses(),
                'family' => $variantProduct->getFamily(),
                'categories' => $variantProduct->getCategories(),
                'created' => $variantProduct->getCreated(),
                'updated' => $variantProduct->getUpdated(),
                'rawValues' => [],
                'uniqueData' => $variantProduct->getUniqueData(),
            ]
        );
    }
}
