<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Query;

use Doctrine\ORM\EntityManagerInterface;
use Pim\Component\Catalog\Model\VariantAttributeSetInterface;

/**
 * Checks if an attribute is used as family variant axis
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeIsAFamilyVariantAxis
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
     * @param string $attributeCode
     *
     * @return bool
     */
    public function execute(string $attributeCode): bool
    {
        $query = $this->entityManager->createQueryBuilder()
            ->select('COUNT(attribute_set.id)')
            ->from(VariantAttributeSetInterface::class, 'attribute_set')
            ->innerJoin('attribute_set.axes', 'attribute')
            ->where('attribute.code = :attribute_code')
            ->setParameter('attribute_code', $attributeCode)
            ->getQuery();

        return 0 < $query->getSingleScalarResult();
    }
}
