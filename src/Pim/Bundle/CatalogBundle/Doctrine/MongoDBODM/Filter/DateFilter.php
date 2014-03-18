<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

/**
 * Date filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateFilter extends BaseFilter
{
    /**
     * {@inheritdoc}
     */
    public function add(AbstractAttribute $attribute, $operator, $value)
    {
        $field = $this->getNormalizedValueField($attribute);

        if ($operator === 'BETWEEN') {
            $fromTime = strtotime($value[0]);
            $toTime   = strtotime($value[1]);
            $this->qb->field($field)->gt($fromTime);
            $this->qb->field($field)->lt($toTime);

        } elseif ($operator === '<') {
            $data = strtotime($value);
            $this->qb->field($field)->lt($data);

        } elseif ($operator === '>') {
            $data = strtotime($value);
            $this->qb->field($field)->gt($data);

        } else {
            $fromTime = strtotime($value['from']);
            $toTime   = strtotime($value['to']);
            $this->qb->addAnd(
                $this->qb->expr()
                    ->addOr($this->qb->expr()->field($field)->lt($fromTime))
                    ->addOr($this->qb->expr()->field($field)->gt($toTime))
            );
        }

        return $this;
    }
}
