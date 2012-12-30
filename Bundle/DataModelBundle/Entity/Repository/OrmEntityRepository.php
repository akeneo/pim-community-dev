<?php
namespace Oro\Bundle\DataModelBundle\Entity\Repository;

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
     * @param string  $alias    alias for entity
     * @param boolean $lazyload use lazy loading
     *
     * @return QueryBuilder $qb
     */
    public function createQueryBuilder($alias, $lazyload = false)
    {
        if ($lazyload) {
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
     * Finds attributes
     *
     * @param array $attributes attribute codes
     *
     * @return array The objects.
     */
    public function getAttributes(array $attributes)
    {
        // TODO to refactor, take a look on getFqcnFromAlias
        $parts = explode("\\", $this->_entityName);
        $entityShortName = $parts[0].$parts[2].':'.$parts[4];
        $attributeSN = 'OroDataModelBundle:OrmEntityAttribute';

        // retrieve attributes
        $alias = 'Attribute';
        $qb = $this->_em->createQueryBuilder()
            ->select($alias)
            ->from($attributeSN, $alias)
            ->andWhere('Attribute.entityType = :type')
            ->setParameter('type', $entityShortName);

        // filter by code
        if (!empty($attributes)) {
            $qb->andWhere($qb->expr()->in('Attribute.code', $attributes));
        }

        // prepare associative array
        $attributes = $qb->getQuery()->getResult();
        $codeToAttribute = array();
        foreach ($attributes as $attribute) {
            $codeToAttribute[$attribute->getCode()]= $attribute;
        }

        return $codeToAttribute;
    }

    /**
     * Finds entities and attributes values by a set of criteria.
     *
     * @param array      $attributes attribute codes
     * @param array      $criteria   criterias
     * @param array|null $orderBy    order by
     * @param int|null   $limit      limit
     * @param int|null   $offset     offset
     *
     * @return array The objects.
     */
    public function findByWithAttributes(array $attributes = null, array $criteria = null, array $orderBy = null, $limit = null, $offset = null)
    {
        // get base query builder (join to attribute and value)
        $qb = $this->createQueryBuilder('Entity');

        // get only asked attributes
        if ($attributes and !empty($attributes)) {
            $qb->andWhere($qb->expr()->in('Attribute.code', $attributes));
        }

        // add criteria
        if ($criteria and !empty($criteria)) {
            // load attributes
            $codeToAttribute = $this->getAttributes($attributes);
            foreach ($criteria as $fieldCode => $fieldValue) {
                // attribute criteria
                if (in_array($fieldCode, $attributes)) {
                    $attribute = $codeToAttribute[$fieldCode];
                    $backend = $attribute->getBackendType();
                    $qb->andWhere('Value.attribute = :att'.$fieldCode.' AND Value.'.$backend.' = :value'.$fieldCode)
                        ->setParameter('att'.$fieldCode, $attribute->getId())
                        ->setParameter('value'.$fieldCode, $fieldValue);
                // field criteria
                } else {
                    $qb->andWhere('Entity.'.$fieldCode.' = :'.$fieldCode)->setParameter($fieldCode, $fieldValue);
                }
            }
        }

        // TODO use leftjoin with to do where cond1 and cond2 on join


        return $qb->getQuery()->getResult();
    }

}
