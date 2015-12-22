<?php

namespace Pim\Bundle\EnrichBundle\Doctrine\ORM\Repository;

use Akeneo\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Pim\Bundle\CatalogBundle\Model\FamilyInterface;

/**
 * Attribute searchable repository
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Soatware License (OSL 3.0)
 */
class AttributeSearchableRepository implements SearchableRepositoryInterface
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var string */
    protected $entityName;

    /**
     * @param EntityManagerInterface $entityManager
     * @param string                 $entityName
     */
    public function __construct(EntityManagerInterface $entityManager, $entityName)
    {
        $this->entityManager = $entityManager;
        $this->entityName    = $entityName;
    }

    /**
     * {@inheritdoc}
     *
     * @return FamilyInterface[]
     */
    public function findBySearch($search = null, array $options = [])
    {
        //TODO: refactor on master because this part is exactly the same that FamilySearchableRepository
        //TODO: and should be put in Akeneo\Bundle\StorageUtilsBundle\Doctrine\ORM\Repository\SearchableRepository
        $qb = $this->entityManager->createQueryBuilder()->select('a')->from($this->entityName, 'a');
        $qb->leftJoin('a.translations', 'at');

        if (null !== $search && '' !== $search) {
            $qb->where('a.code like :search')->setParameter('search', "%$search%");
            $qb->orWhere('at.label like :search')->setParameter('search', "%$search%");
        }

        if (isset($options['identifiers']) && is_array($options['identifiers']) && !empty($options['identifiers'])) {
            $qb->andWhere('a.code in (:codes)');
            $qb->setParameter('codes', $options['identifiers']);
        }

        if (isset($options['limit'])) {
            $qb->setMaxResults((int) $options['limit']);
            if (isset($options['page'])) {
                $qb->setFirstResult((int) $options['limit'] * ((int) $options['page'] - 1));
            }
        }



        return $qb->getQuery()->getResult();
    }
}
