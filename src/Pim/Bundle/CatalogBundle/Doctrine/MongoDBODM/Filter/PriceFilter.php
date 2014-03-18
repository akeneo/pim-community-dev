<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

/**
 * Price filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceFilter extends BaseFilter
{
    /**
     * {@inheritdoc}
     */
    public function add(AbstractAttribute $attribute, $operator, $value)
    {
        list($data, $currency) = explode(' ', $value);
        $data = (float) $data;

        $field = $this->getNormalizedValueField($attribute);
        $field = sprintf('%s.%s', $field, $currency);
        
        $fieldData = sprintf('%s.data', $field);

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
