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

    /**
     * Locale code
     * @var string
     */
    protected $defaultLocaleCode;

    /**
     * Locale code
     * @var string
     */
    protected $localeCode;

    /**
     * Use lazy loading ? TODO: delete ?
     * @var boolean
     */
    protected $useLazyLoading;

    /**
     * Override to deal with default locale and lazy loading
     * TODO : use default locale from app conf
     * TODO : single table inheritance for flatten mode ?
     *
     * @param EntityManager $em    The EntityManager to use.
     * @param ClassMetadata $class The class descriptor.
     */
    public function __construct($em, \Doctrine\ORM\Mapping\ClassMetadata $class)
    {
        parent::__construct($em, $class);

        $this->useLazyLoading = false;
    }

    /**
     * Get default locale code
     *
     * @return string
     */
    public function getDefaultLocaleCode()
    {
        return $this->defaultlocaleCode;
    }

    /**
     * Set locale code
     *
     * @param string $code
     *
     * @return \Oro\Bundle\DataModelBundle\Entity\OrmEntityRepository
     */
    public function setDefaultLocaleCode($code)
    {
        $this->defaultlocaleCode = $code;

        return $this;
    }

    /**
     * Get locale code
     *
     * @return string
     */
    public function getLocaleCode()
    {
        return $this->localeCode;
    }

    /**
     * Set locale code
     *
     * @param string $code
     *
     * @return \Oro\Bundle\DataModelBundle\Entity\OrmEntityRepository
     */
    public function setLocaleCode($code)
    {
        $this->localeCode = $code;

        return $this;
    }

    /**
     * Create a new QueryBuilder instance that is prepopulated for this entity name
     *
     * @param string $alias
     *
     * @return QueryBuilder $qb
     */
    public function createQueryBuilder($alias)
    {
        if ($this->useLazyLoading) {
            $qb = parent::createQueryBuilder($alias);
        } else {
            // if no lazy loading directly join with values and attribute
            $qb = $this->_em->createQueryBuilder();
            $qb->select($alias, 'Value', 'Attribute')
                ->from($this->_entityName, $alias)
                ->leftJoin($alias.'.values', 'Value')
                ->leftJoin('Value.attribute', 'Attribute')
                // if no translatable, get default locale value
                // if translatable, get asked locale value
                // there is no fallback defined
                ->andWhere(
                    '(Attribute.translatable = 1 AND Value.localeCode = :locale) '
                    .'OR (Attribute.translatable = 0 and Value.localeCode = :defaultLocale) '
                    .'OR (Value.localeCode IS NULL)'
                )
                ->setParameter('defaultLocale', $this->getDefaultLocaleCode())
                ->setParameter('locale', $this->getLocaleCode());
        }

        return $qb;
    }

    /**
     * Find all entities
     *
     * @return multitype:
     */
    public function findAllEntities()
    {
        $qb = $this->createQueryBuilder('Entity');

        return $qb->getQuery()->getResult();
    }

}
