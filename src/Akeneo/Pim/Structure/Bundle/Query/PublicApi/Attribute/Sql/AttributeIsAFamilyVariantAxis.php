<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\Attribute\Sql;

use Akeneo\Pim\Structure\Component\Model\VariantAttributeSetInterface;
use Doctrine\ORM\EntityManagerInterface;

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

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

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
