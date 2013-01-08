<?php
namespace Oro\Bundle\FlexibleEntityBundle\Entity\Repository;

use Oro\Bundle\FlexibleEntityBundle\Exception\UnknownAttributeException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Base repository for flexible entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class OrmFlexibleEntityRepository extends EntityRepository
{

    /**
     * Flexible entity config
     * @var array
     */
    protected $flexibleConfig;

    /**
     * Locale code
     * @var string
     */
    protected $localeCode;

    /**
     * Set flexible entity config

     * @param array $config
     *
     * @return OrmFlexibleEntityRepository
     */
    public function setFlexibleConfig($config)
    {
        $this->flexibleConfig = $config;

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
     * @return OrmFlexibleEntityRepository
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
     * @param array $attributeCodes attribute codes
     *
     * @throws UnknownAttributeException
     *
     * @return array The objects.
     */
    public function getCodeToAttributes(array $attributeCodes)
    {
        // prepare entity attributes query
        $attributeAlias = 'Attribute';
        $attributeName = $this->flexibleConfig['flexible_attribute_class'];
        $attributeRepo = $this->_em->getRepository($attributeName);
        $qb = $attributeRepo->createQueryBuilder($attributeAlias);
        $qb->andWhere('Attribute.entityType = :type')->setParameter('type', $this->_entityName);

        // filter by code
        if (!empty($attributeCodes)) {
            $qb->andWhere($qb->expr()->in('Attribute.code', $attributeCodes));
        }

        // prepare associative array
        $attributes = $qb->getQuery()->getResult();
        $codeToAttribute = array();
        foreach ($attributes as $attribute) {
            $codeToAttribute[$attribute->getCode()]= $attribute;
        }

        // raise exception
        if (!empty($attributeCodes) and count($attributeCodes) != count($codeToAttribute)) {
            $missings = array_diff($attributeCodes, array_keys($codeToAttribute));
            throw new UnknownAttributeException('Attribute(s) with code '.implode(', ', $missings).' not exists for entity '.$this->_entityName);
        }

        return $codeToAttribute;
    }

    /**
     * Find flexible attribute by code
     *
     * @param string $code
     *
     * @throws UnknownAttributeException
     *
     * @return AbstractOrmEntityAttribute
     */
    public function findAttributeByCode($code)
    {
        $attributeName = $this->flexibleConfig['flexible_attribute_class'];
        $attributeRepo = $this->_em->getRepository($attributeName);
        $attribute = $attributeRepo->findOneBy(array('entityType' => $this->_entityName, 'code' => $code));

        return $attribute;
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
            $codeToAttribute = $this->getCodeToAttributes($attributes);
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
            $attributeCodeToAlias = $this->addFieldOrAttributeCriterias($qb, $attributes, $criteria, $codeToAttribute, $attributeCodeToAlias);
        }
        // get selected attributes values (but not used as criteria)
        if (!empty($attributes)) {
            $attributeCodeToAlias = $this->addAttributeToSelect($qb, $attributes, $codeToAttribute, $attributeCodeToAlias, $orderBy);
        }
        // add order by
        if ($orderBy) {
            $this->addFieldOrAttributeOrderBy($qb, $orderBy, $attributeCodeToAlias);
        }
        // add limit
        if (!is_null($offset) and !is_null($limit)) {
            $qb->setFirstResult($offset)->setMaxResults($limit);
            // use doctrine paginator to avoid count problem with left join of values
            $paginator = new Paginator($qb->getQuery(), $fetchJoinCollection = true);

            return $paginator;
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Add attributes to select
     *
     * @param QueryBuilder $qb                   query builder to update
     * @param array        $attributes           attributes to select
     * @param array        $codeToAttribute      attribute code to attribute
     * @param array        $attributeCodeToAlias attribute code to query alias
     * @param array        $orderBy              attribute to order by, aims to determine if we get a localized value
     *
     * @return array $attributeCodeToAlias
     */
    protected function addAttributeToSelect($qb, $attributes, $codeToAttribute, $attributeCodeToAlias, $orderBy = array())
    {
        foreach ($attributes as $attributeCode) {
            // add select attribute value
            $joinAlias = 'selectValue'.$attributeCode;
            $qb->addSelect($joinAlias);
            // prepare join condition
            $attribute = $codeToAttribute[$attributeCode];
            $joinValue = 'selectvalue'.$attributeCode;
            $condition = $joinAlias.'.attribute = '.$attribute->getId();
            // add condition to get only translated value if we use this attribute to order
            if ($attribute->getTranslatable() and isset($orderBy[$attributeCode])) {
                $joinValueLocale = 'selectlocale'.$attributeCode;
                $condition .= ' AND '.$joinAlias.'.localeCode = :'.$joinValueLocale;
                $qb->setParameter($joinValueLocale, $this->getLocaleCode());
            }
            // add the join with condition and store alias for next uses
            $qb->leftJoin('Entity.'.$attribute->getBackendModel(), $joinAlias, 'WITH', $condition);
            $attributeCodeToAlias[$attributeCode]= $joinAlias.'.'.$attribute->getBackendType();
        }

        return $attributeCodeToAlias;
    }

    /**
     * Add fields and/or attributes criterias
     *
     * @param QueryBuilder $qb                   query builder to update
     * @param array        $attributes           attributes to select
     * @param array        $criteria             criterias on field or attribute
     * @param array        $codeToAttribute      attribute code to attribute
     * @param array        $attributeCodeToAlias attribute code to query alias
     *
     * @return array $attributeCodeToAlias
     */
    protected function addFieldOrAttributeCriterias($qb, $attributes, $criteria, $codeToAttribute, $attributeCodeToAlias)
    {
        foreach ($criteria as $fieldCode => $fieldValue) {
            // add attribute criteria
            if (in_array($fieldCode, $attributes)) {
                // prepare condition
                $attribute = $codeToAttribute[$fieldCode];
                $joinAlias = 'filterValue'.$fieldCode;
                $joinValue = 'filtervalue'.$fieldCode;
                $condition = $joinAlias.'.attribute = '.$attribute->getId()
                    .' AND '.$joinAlias.'.'.$attribute->getBackendType().' = :'.$joinValue;
                // add condition on locale if attribute is translatable
                if ($attribute->getTranslatable()) {
                    $joinValueLocale = 'filterlocale'.$fieldCode;
                    $condition .= ' AND '.$joinAlias.'.localeCode = :'.$joinValueLocale;
                    $qb->setParameter($joinValueLocale, $this->getLocaleCode());
                }
                // add inner join to filter lines and store value alias for next uses
                $qb->innerJoin('Entity.'.$attribute->getBackendModel(), $joinAlias, 'WITH', $condition)
                    ->setParameter($joinValue, $fieldValue);
                $attributeCodeToAlias[$fieldCode]= $joinAlias.'.'.$attribute->getBackendType();

            // add field criteria
            } else {
                $qb->andWhere('Entity.'.$fieldCode.' = :'.$fieldCode)->setParameter($fieldCode, $fieldValue);
            }
        }

        return $attributeCodeToAlias;
    }

    /**
     * Add fields and/or attributes order by
     *
     * @param QueryBuilder $qb                   query builder to update
     * @param array        $orderBy              fields and attributes order by
     * @param array        $attributeCodeToAlias attribute code to query alias
     */
    protected function addFieldOrAttributeOrderBy($qb, $orderBy, $attributeCodeToAlias)
    {
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
