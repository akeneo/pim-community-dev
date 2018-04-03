<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityRepository;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;

/**
 * Repository
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyRepository extends EntityRepository implements FamilyRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFullRequirementsQB(FamilyInterface $family, $localeCode)
    {
        $qb = $this->getEntityManager()
            ->getRepository('Pim\Bundle\CatalogBundle\Entity\AttributeRequirement')
            ->createQueryBuilder('r')
            ->select('r, a, t')
            ->leftJoin('r.attribute', 'a');

        if (null !== $localeCode) {
            $qb->leftJoin('a.translations', 't', 'WITH', 't.locale = :localeCode')
                ->setParameter('localeCode', $localeCode);
        } else {
            $qb->leftJoin('a.translations', 't');
        }

        $qb->where('r.family = :family')
            ->setParameter('family', $family);

        return $qb;
    }

    /**
     * Get families with family variants
     *
     * @param  string $search
     * @param  array  $options
     * @param  int    $limit
     *
     * @return array
     */
    public function getWithVariants($search = null, array $options = [], int $limit = null): array
    {
        $qb = $this->createQueryBuilder('f')->where('f.familyVariants IS NOT EMPTY');

        if (null !== $search && '' !== $search) {
            if (!isset($options['locale'])) {
                $qb->andWhere('f.code like :search')->setParameter('search', '%' . $search . '%');
            } else {
                $qb->leftJoin('f.translations', 'ft');
                $qb->andWhere('f.code like :search OR (ft.label like :search AND ft.locale = :locale)');
                $qb->setParameter('search', '%' . $search . '%');
                $qb->setParameter('locale', $options['locale']);
            }
        }

        if ($limit) {
            $qb->setMaxResults((int) $limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getFullFamilies(FamilyInterface $family = null, ChannelInterface $channel = null)
    {
        $qb = $this->createQueryBuilder('f')
            ->select('f, c, l, r, a, cu')
            ->join('f.requirements', 'r')
            ->join('r.attribute', 'a')
            ->join('r.channel', 'c')
            ->join('c.locales', 'l')
            ->join('c.currencies', 'cu')
            ->where('r.required = 1');

        if (null !== $channel) {
            $qb->andWhere('r.channel = :channel')
                ->setParameter('channel', $channel);
        }

        if (null !== $family) {
            $qb->andWhere('f.id = :familyId')
                ->setParameter('familyId', $family->getId());
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}*
     */
    public function findByIds(array $familyIds)
    {
        if (empty($familyIds)) {
            throw new \InvalidArgumentException('Array must contain at least one family id');
        }

        $qb = $this->createQueryBuilder('f');
        $qb->where($qb->expr()->in('f.id', ':family_ids'));
        $qb->setParameter(':family_ids', $familyIds);

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($code)
    {
        return $this->findOneBy(['code' => $code]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['code'];
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttribute($id, $attributeCode)
    {
        $queryBuilder = $this->createQueryBuilder('f')
            ->select(1)
            ->innerJoin('f.attributes', 'a')
            ->where('f.id = :id')
            ->andWhere('a.code = :code')
            ->setMaxResults(1)
            ->setParameters([
                'id'   => $id,
                'code' => $attributeCode,
            ]);

        $result = $queryBuilder->getQuery()->getArrayResult();

        return count($result) > 0;
    }
}
