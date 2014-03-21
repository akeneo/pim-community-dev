<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * Metric filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MetricFilter extends BaseFilter
{
    /**
     * {@inheritdoc}
     */
    public function addAttributeFilter(AbstractAttribute $attribute, $operator, $value)
    {
        $data = (float) $value;

        $field = $this->getNormalizedValueField($attribute);
        $fieldData = sprintf('%s.baseData', $field);

        switch ($operator) {
            case '<':
                $this->qb->field($fieldData)->lt($data);
                break;
            case '<=':
                $this->qb->field($fieldData)->lte($data);
                break;
            case '>':
                $this->qb->field($fieldData)->gt($data);
                break;
            case '>=':
                $this->qb->field($fieldData)->gte($data);
                break;
            default:
                $this->qb->field($fieldData)->equals($data);
        }

        return $this;
    }
}
