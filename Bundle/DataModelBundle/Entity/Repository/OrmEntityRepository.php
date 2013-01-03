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
                ->leftJoin('Value.attribute', 'Attribute');
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
        // identify kind of query
        $hasSelectedAttributes = (!is_null($attributes) and !empty($attributes));
        $hasCriterias = (!is_null($criteria) and !empty($criteria));
        if ($hasCriterias) {
            $attributeCriterias = array_intersect($attributes, array_keys($criteria));
            $fieldCriterias     = array_diff(array_keys($criteria), $attributes);
        }
        if ($hasCriterias or $hasSelectedAttributes) {
            $codeToAttribute = $this->getAttributes($attributes);
        }
        // get base query builder (direct join to attribute and value if no attribute selection)
        if (!$hasSelectedAttributes) {
            $qb = $this->createQueryBuilder('Entity');
        } else {
            $qb = $this->createQueryBuilder('Entity', true); // lazy load
        }
        // add criterias
        $attributeCodeToAlias = array();
        if ($criteria and !empty($criteria)) {
            foreach ($criteria as $fieldCode => $fieldValue) {
                // add attribute criteria
                if (in_array($fieldCode, $attributes)) {
                    // prepare condition
                    $attribute       = $codeToAttribute[$fieldCode];
                    $joinAlias       = 'cValue'.$fieldCode;
                    $joinValue       = 'cvalue'.$fieldCode;
                    $joinValueLocale = 'clocale'.$fieldCode;
                    $condition = $joinAlias.'.attribute = '.$attribute->getId()
                        .' AND '.$joinAlias.'.'.$attribute->getBackendType().' = :'.$joinValue
                        .' AND '.$joinAlias.'.localeCode = :'.$joinValueLocale;
                    $condLocale = ($attribute->getTranslatable()) ? $this->getLocaleCode() : $this->getDefaultLocaleCode();
                    // add inner join to filter lines
                    $qb->innerJoin('Entity.'.$attribute->getBackendModel(), $joinAlias, 'WITH', $condition)
                        ->setParameter($joinValue, $fieldValue)
                        ->setParameter($joinValueLocale, $condLocale);
                    $attributeCodeToAlias[$fieldCode]= $joinAlias.'.'.$attribute->getBackendType();
                // add field criteria
                } else {
                    $qb->andWhere('Entity.'.$fieldCode.' = :'.$fieldCode)->setParameter($fieldCode, $fieldValue);
                }
            }
        }
        // get selected attributes values (but not used as criteria)
        if (!empty($attributes)) {
            foreach ($attributes as $attributeCode) {
                // preare join condition
                $attribute    = $codeToAttribute[$attributeCode];
                $joinAlias    = 'sValue'.$attributeCode;
                $joinValue    = 'svalue'.$attributeCode;
                $condition = $joinAlias.'.attribute = '.$attribute->getId();
                // add select attribute value
                $qb->addSelect($joinAlias);
                $qb->leftJoin('Entity.'.$attribute->getBackendModel(), $joinAlias, 'WITH', $condition);
                $attributeCodeToAlias[$attributeCode]= $joinAlias.'.'.$attribute->getBackendType();
            }
        }
        // add order by
        if ($orderBy) {
            foreach ($orderBy as $fieldCode => $direction) {
                // on attribute value
                if (isset($attributeCodeToAlias[$fieldCode])) {
                    $qb->addOrderBy($attributeCodeToAlias[$fieldCode], $direction);
                // on entity field
                } else {
                    $qb->addOrderBy('Entity.'.$fieldCode, $direction);
                }
            }
        }
        // add limit
        if (!is_null($offset) and !is_null($limit)) {
            $qb->setFirstResult($offset)->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Find entity with attributes values
     *
     * @param int $id entity id
     *
     * @return Entity the entity
     */
    public function findWithAttributes($id)
    {
        $products = $this->findByWithAttributes(array(), array('id' => $id));

        return current($products);
    }

}
