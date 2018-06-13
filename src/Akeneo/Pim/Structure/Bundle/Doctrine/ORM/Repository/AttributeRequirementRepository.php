<?php

namespace Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository;

use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRequirementRepositoryInterface;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;

/**
 * Repository for attribute requirement entity
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeRequirementRepository extends EntityRepository implements AttributeRequirementRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findRequiredAttributesCodesByFamily(FamilyInterface $family)
    {
        $qb = $this->createQueryBuilder('ar');
        $qb
            ->select('a.code AS attribute, c.code AS channel')
            ->innerJoin('ar.attribute', 'a')
            ->innerJoin('ar.channel', 'c')
            ->where('ar.family = :family')
            ->andWhere('ar.required = :required')
            ->orderBy('c.code', Criteria::ASC)
            ->addOrderBy('a.code', Criteria::ASC)
            ->setParameter(':family', $family)
            ->setParameter(':required', true);

        return $qb->getQuery()->getArrayResult();
    }
}
