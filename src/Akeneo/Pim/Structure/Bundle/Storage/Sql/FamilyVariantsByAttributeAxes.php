<?php
declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Storage\Sql;

use Akeneo\Pim\Structure\Component\FamilyVariant\Query\FamilyVariantsByAttributeAxesInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyVariant;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Find family variants identifiers by their attribute axes.
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyVariantsByAttributeAxes implements FamilyVariantsByAttributeAxesInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public function findIdentifiers(array $attributeAxesCodes): array
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();

        $queryBuilder
            ->select('family_variant.code')
            ->from(FamilyVariant::class, 'family_variant')
            ->innerJoin('family_variant.variantAttributeSets', 'variant_attribute_sets')
            ->innerJoin('variant_attribute_sets.axes', 'axes')
            ->where('axes.code IN (:attribute_codes)')
            ->setParameter('attribute_codes', $attributeAxesCodes);

        $codes = $queryBuilder->getQuery()
            ->getArrayResult();

        return array_map(
            function ($data) {
                return $data['code'];
            },
            $codes
        );
    }
}
