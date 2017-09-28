<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\UnexpectedResultException;
use Pim\Component\Catalog\Repository\GroupTypeRepositoryInterface;

/**
 * Group type repository
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupTypeRepository extends EntityRepository implements GroupTypeRepositoryInterface
{
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
    public function findTypeIds($isVariant)
    {
        $query = $this->_em->createQueryBuilder()
            ->select('g.id')
            ->from($this->_entityName, 'g', 'g.id')
            ->leftJoin('g.translations', 't')
            ->andWhere('g.variant = :variant')
            ->setParameter('variant', $isVariant)
            ->getQuery();

        return array_keys($query->getArrayResult());
    }
}
