<?php
namespace Oro\Bundle\DataModelBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Base repository for flexible entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class OrmEntityRepository extends EntityRepository
{

    protected $localeCode;

    protected $useLazyLoading;

    /**
     * Override to deal with default locale and lazy loading
     * TODO : use default locale from app conf
     * TODO : single table inheritance for flatten mode ?
     *
     * @param EntityManager $em The EntityManager to use.
     * @param ClassMetadata $classMetadata The class descriptor.
     */
    public function __construct($em, \Doctrine\ORM\Mapping\ClassMetadata $class)
    {
        parent::__construct($em, $class);

        // TODO en or en_US
        $this->localeCode = 'en_US';
        $this->useLazyLoading = false;
    }

    public function getLocaleCode()
    {
        return $this->localeCode;
    }

    public function setLocaleCode($code)
    {
        $this->localeCode = $code;

        return $this;
    }

    /**
     * Create a new QueryBuilder instance that is prepopulated for this entity name
     *
     * @param string $alias
     * @return QueryBuilder $qb
     */
    public function createQueryBuilder($alias)
    {
        if ($this->useLazyLoading) {
            $qb = parent::createQueryBuilder($alias);
        } else {
            // if no lazy loading directly join with values and attribute
            $qb = $this->_em->createQueryBuilder()
                ->select($alias, 'Value', 'Attribute')
                ->from($this->_entityName, $alias)
                ->leftJoin($alias.'.values', 'Value')
                ->leftJoin('Value.attribute', 'Attribute');
        }
        return $qb;
    }

    public function findAllEntities()
    {
        $qb = $this->createQueryBuilder('Entity');

        return $qb->getQuery()->getResult();
    }


    /**
     * Finds entities by a set of criteria.
     *
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     * @return array The objects.
     *
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $persister = $this->_em->getUnitOfWork()->getEntityPersister($this->_entityName);
        see loadManyToManyCollection
        return $persister->loadAll($criteria, $orderBy, $limit, $offset);
    }
    */


}
