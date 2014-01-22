<?php

namespace Pim\Bundle\CatalogBundle\Tests\Entity;

/**
 * Atribute Group Test entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupTestEntity extends ObjectTestEntity
{
    /**
     * Set properties from array
     *
     * @param array $datas
     *
     * @return \Pim\Bundle\CatalogBundle\Tests\Entity\ObjectEntityTest
     */
    public function fromArray($datas)
    {
        foreach ($datas as $key => $value) {
            $method = 'set'. ucfirst($key);
            if ($key === 'sort_order') {
                $method = 'setSortOrder';
            }
            if (method_exists(get_class($this->entity), $method)) {
                $this->entity->$method($value);
            }
        }

        return $this;
    }
}
