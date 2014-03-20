<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\MongoDBODM\Filter;

use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * Entity filter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntityFilter extends BaseFilter
{
    /**
     * {@inheritdoc}
     */
    public function add(AbstractAttribute $attribute, $operator, $value)
    {
        $field = $this->getNormalizedValueField($attribute);
        $field = sprintf('%s.id', $field);
        $value = array_map('intval', $value);
        $this->qb->field($field)->in($value);

        return $this;
    }
}
