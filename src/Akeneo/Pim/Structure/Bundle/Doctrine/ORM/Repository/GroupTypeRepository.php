<?php

namespace Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository;

use Akeneo\Pim\Structure\Component\Repository\GroupTypeRepositoryInterface;
use Doctrine\ORM\EntityRepository;

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
    public function findTypeIds()
    {
        $query = $this->_em->createQueryBuilder()
            ->select('g.id')
            ->from($this->_entityName, 'g', 'g.id')
            ->leftJoin('g.translations', 't')
            ->getQuery();

        return array_keys($query->getArrayResult());
    }
}
