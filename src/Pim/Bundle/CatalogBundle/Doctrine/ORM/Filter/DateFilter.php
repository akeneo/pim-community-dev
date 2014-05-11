<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Filter;

/**
 * Date filter
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateFilter extends BaseFilter
{
    /**
     * {@inheritdoc}
     */
    public function addFieldFilter($field, $operator, $value)
    {
        $field = current($this->qb->getRootAliases()).'.'.$field;
        $conditions = array();
        
        switch ($operator) {
            case 'BETWEEN':
                $conditions[] = $this->qb->expr()->gt($field, $this->qb->expr()->literal($this->getDateValue($value[0])));
                $conditions[] = $this->qb->expr()->lt($field, $this->qb->expr()->literal($this->getDateValue($value[1], true)));
                break;

            case '>':
                $conditions[] = $this->qb->expr()->gt($field, $this->qb->expr()->literal($this->getDateValue($value, true)));
                break;

            case '<':
                $conditions[] = $this->qb->expr()->lt($field, $this->qb->expr()->literal($this->getDateValue($value)));
                break;

            case '=':
                $conditions[] = $this->qb->expr()->gt($field, $this->qb->expr()->literal($this->getDateValue($value)));
                $conditions[] = $this->qb->expr()->lt($field, $this->qb->expr()->literal($this->getDateValue($value, true)));
                break;

            case 'EMPTY':
                $conditions[] = $this->qb->expr()->isNull($field);
                break;

            default:
                $conditions[] = $this->qb->expr()->orX(
                    $this->qb->expr()->lt($field, $this->qb->expr()->literal($this->getDateValue($value['from']))),
                    $this->qb->expr()->gt($field, $this->qb->expr()->literal($this->getDateValue($value['to'], true)))
                );
                break;
        }

        foreach ($conditions as $condition) {
            $this->qb->andWhere($condition);
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
    private function getDateValue($data, $endOfDay = false)
    {
        if ($data instanceof \DateTime && true === $endOfDay) {
            $data->setTime(23, 59, 59);
        }
        elseif (!$data instanceof \DateTime && true === $endOfDay) {
            $data = sprintf('%s 23:59:59', $data);
        }

        return $data instanceof \DateTime ? $data->format('Y-m-d H:i:s') : $data;
    }
}
