<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Doctrine\FilterInterface;

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
        // TODO: prepare value
        $value = str_replace('%', '', $value);

        $elemMatch = $this->qb->expr()
            ->field('attribute')->equals($attribute->getId())
            ->field($attribute->getBackendType())->equals($value);

        if ($attribute->isScopable()) {
            $elemMatch->field('scope')->equals($this->scope);
        }
        if ($attribute->isLocalizable()) {
            $elemMatch->field('locale')->equals($this->locale);
        }

        $expression = $this->qb->expr()
            ->field('values')
            ->elemMatch($elemMatch);

        $this->qb->addAnd($expression);

        return $this;
    }
}
