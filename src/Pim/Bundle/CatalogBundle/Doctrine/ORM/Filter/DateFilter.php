<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

use Pim\Bundle\CatalogBundle\Doctrine\Query\FieldFilterInterface;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;

/**
 * Date filter
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * TODO : should implement attribute filter interface
 * TODO : "not between" operator doesnt work
 */
class DateFilter implements FieldFilterInterface
{
    /**
     * @var QueryBuilder
     */
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
    public function addFieldFilter($field, $operator, $value)
    {
        $field = current($this->qb->getRootAliases()).'.'.$field;

        switch ($operator) {
            case 'BETWEEN':
                $this->qb->andWhere(
                    $this->qb->expr()->andX(
                        $this->qb->expr()->gt($field, $this->getDateLiteralExpr($value[0])),
                        $this->qb->expr()->lt($field, $this->getDateLiteralExpr($value[1], true))
                    )
                );
                break;

            case '>':
                $this->qb->andWhere($this->qb->expr()->gt($field, $this->getDateLiteralExpr($value, true)));
                break;

            case '<':
                $this->qb->andWhere($this->qb->expr()->lt($field, $this->getDateLiteralExpr($value)));
                break;

            case '=':
                $this->qb->andWhere(
                    $this->qb->expr()->andX(
                        $this->qb->expr()->gt($field, $this->getDateLiteralExpr($value)),
                        $this->qb->expr()->lt($field, $this->getDateLiteralExpr($value, true))
                    )
                );
                break;

            case 'EMPTY':
                $this->qb->andWhere($this->qb->expr()->isNull($field));
                break;

            default:
                $this->qb->andWhere(
                    $this->qb->expr()->orX(
                        $this->qb->expr()->lt($field, $this->getDateLiteralExpr($value['from'])),
                        $this->qb->expr()->gt($field, $this->getDateLiteralExpr($value['to'], true))
                    )
                );
                break;
        }

        return $this;
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
    public function supportsOperator($operator)
    {
        return in_array(
            $operator,
            ['=', '<', '>', 'BETWEEN', 'EMPTY']
        );
    }

    /**
     * Get the literal expression of the date
     *
     * @param string  $data
     * @param boolean $endOfDay
     *
     * @return Literal
     */
    protected function getDateLiteralExpr($data, $endOfDay = false)
    {
        return $this->qb->expr()->literal($this->getDateValue($data, $endOfDay));
    }

    /**
     * Get the date formatted from data
     *
     * @param \DateTime|string $data
     * @param boolean          $endOfDay
     *
     * @return string
     */
    protected function getDateValue($data, $endOfDay = false)
    {
        if ($data instanceof \DateTime && true === $endOfDay) {
            $data->setTime(23, 59, 59);
        } elseif (!$data instanceof \DateTime && true === $endOfDay) {
            $data = sprintf('%s 23:59:59', $data);
        }

        return $data instanceof \DateTime ? $data->format('Y-m-d H:i:s') : $data;
    }
}
