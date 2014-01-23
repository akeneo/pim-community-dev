<?php

namespace Pim\Bundle\CatalogBundle\Tests\Entity;

/**
 * Object Test entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ObjectTestEntity
{
    /**
     * @var mixed
     */
    protected $entity;

    /**
     * Constructor
     * @param string $entityClass Class name of entity tested
     * @param array  $datas       Data values for entity tested
     */
    public function __construct($entityClass, array $datas = [])
    {
        $this->entity = new $entityClass();
        $this->fromArray($datas);
    }

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
            if (method_exists(get_class($this->entity), $method)) {
                $this->entity->$method($value);
            }
        }

        return $this;
    }

    /**
     * Get tested entity
     *
     * @return mixed
     */
    public function getTestedEntity()
    {
        return $this->entity;
    }
}
