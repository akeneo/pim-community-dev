<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Query;

use Doctrine\ORM\EntityManagerInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Pim\Component\Catalog\EntityWithFamily\Query;

/**
 * Query that turns  a product into a variant product
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TurnProduct implements Query\TurnProduct
{
    private const PRODUCT_VARIANT_TYPE = 'variant_product';

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var NormalizerInterface */
    private $normalizer;

    /**
     * @param EntityManagerInterface $entityManager
     * @param NormalizerInterface $normalizer
     */
    public function __construct(EntityManagerInterface $entityManager, NormalizerInterface $normalizer)
    {
        $this->entityManager = $entityManager;
        $this->normalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function into(VariantProductInterface $variantProduct): void
    {
        $values = $this->normalizer->normalize($variantProduct->getValuesForVariation(), 'storage');

        $sql = <<<SQL
UPDATE pim_catalog_product AS variant_product
SET 
    variant_product.product_model_id = :product_model_id, 
    variant_product.raw_values = :raw_values, 
    variant_product.product_type = :product_type
WHERE (id = :id)
SQL;

        $this->entityManager->getConnection()->executeQuery($sql, [
            'product_model_id' => $variantProduct->getParent()->getId(),
            'raw_values' => json_encode($values),
            'product_type' => self::PRODUCT_VARIANT_TYPE,
            'id' => $variantProduct->getId()
        ]);
    }
}
