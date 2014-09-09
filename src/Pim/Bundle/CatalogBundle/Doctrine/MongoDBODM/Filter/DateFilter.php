<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Doctrine\ODM\MongoDB\Query\Builder as QueryBuilder;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Doctrine\Query\AttributeFilterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\Query\FieldFilterInterface;
use Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\ProductQueryUtility;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;

/**
 * Date filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateFilter implements AttributeFilterInterface, FieldFilterInterface
{
    /** @var QueryBuilder */
    protected $qb;

    /** @var CatalogContext */
    protected $context;

    /**
     * Instanciate the filter
     *
     * @param CatalogContext $context
     */
    public function __construct(CatalogContext $context)
    {
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function setQueryBuilder($queryBuilder)
    {
        $this->qb = $queryBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsField($field)
    {
        return in_array($field, ['created', 'updated']);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsAttribute(AbstractAttribute $attribute)
    {
        return $attribute->getAttributeType() === 'pim_catalog_date';
    }

    /**
     * {@inheritdoc}
     */
    public function supportsOperator($operator)
    {
        return in_array($operator, ['BETWEEN', '=', '<', '>', 'EMPTY']);
    }

    /**
     * {@inheritdoc}
     */
    public function addAttributeFilter(AbstractAttribute $attribute, $operator, $value)
    {
        $field = ProductQueryUtility::getNormalizedValueFieldFromAttribute($attribute, $this->context);
        $this->addFieldFilter($field, $operator, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value)
    {
        $field = sprintf('%s.%s', ProductQueryUtility::NORMALIZED_FIELD, $field);

        switch ($operator) {
            case 'BETWEEN':
                $this->qb->field($field)->gte($this->getTimestamp($value[0]));
                $this->qb->field($field)->lte($this->getTimestamp($value[1], true));
                break;

            case '>':
                $this->qb->field($field)->gt($this->getTimestamp($value, true));
                break;

            case '<':
                $this->qb->field($field)->lt($this->getTimestamp($value));
                break;

            case '=':
                $this->qb->field($field)->gte($this->getTimestamp($value));
                $this->qb->field($field)->lte($this->getTimestamp($value, true));
                break;

            case 'EMPTY':
                $this->qb->field($field)->exists(false);
                break;

            default:
                $this->qb->addAnd(
                    $this->qb->expr()
                        ->addOr($this->qb->expr()->field($field)->lte($this->getTimestamp($value['from'])))
                        ->addOr($this->qb->expr()->field($field)->gte($this->getTimestamp($value['to'], true)))
                );
        }

        return $this;
    }

    /**
     * Get timestamp from data
     *
     * @param \DateTime|string $data
     * @param boolean          $endOfDay
     *
     * @return integer
     */
    protected function getTimestamp($data, $endOfDay = false)
    {
        if ($data instanceof \DateTime && true === $endOfDay) {
            $data->setTime(23, 59, 59);
        } elseif (!$data instanceof \DateTime && true === $endOfDay) {
            $data = sprintf('%s 23:59:59', $data);
        }

        return $data instanceof \DateTime ? $data->getTimestamp() : strtotime($data);
    }
}
