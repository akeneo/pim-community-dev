<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Doctrine\FilterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductQueryUtility;
use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;

/**
 * Base filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BaseFilter implements FilterInterface
{
    /**
     * QueryBuilder
     * @var QueryBuilder
     */
    protected $qb;

    /**
     * Locale code
     * @var string
     */
    protected $locale;

    /**
     * Scope code
     * @var string
     */
    protected $scope;

    /**
     * Alias counter, to avoid duplicate alias name
     * @return integer
     */
    protected $aliasCounter = 1;

    /**
     * Instanciate a filter
     *
     * @param QueryBuilder $qb
     * @param string       $locale
     * @param scope        $scope
     */
    public function __construct(QueryBuilder $qb, $locale, $scope)
    {
        $this->qb     = $qb;
        $this->locale = $locale;
        $this->scope  = $scope;
    }

    /**
     * {@inheritdoc}
     */
    public function add(AbstractAttribute $attribute, $operator, $value)
    {
        $field = $this->getNormalizedValueField($attribute);
        if (strpos($value, '/') !== false) {
            $value = new \MongoRegex($value);
        }
        $this->qb->field($field)->equals($value);

        return $this;
    }

    /**
     * @param AbstractAttribute $attribute
     *
     * @return string
     */
    protected function getNormalizedValueField(AbstractAttribute $attribute)
    {
        $field = ProductQueryUtility::getNormalizedValueFieldFromAttribute($attribute, $this->locale, $this->scope);

        return sprintf('%s.%s', ProductQueryUtility::NORMALIZED_FIELD, $field);
    }
}
