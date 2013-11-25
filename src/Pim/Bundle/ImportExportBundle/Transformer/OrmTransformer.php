<?php

namespace Pim\Bundle\ImportExportBundle\Transformer;

/**
 * Transforms an array in an entity
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OrmTransformer extends AbstractOrmTransformer
{
    /**
     * Transforms an array into an entity
     *
     * @param  string $class
     * @param  array  $data
     * @param  array  $mapping
     * @param  array  $defaults
     * @return object
     */
    public function transform($class, array $data, array $mapping = array(), array $defaults = array())
    {
        return $this->doTransform($class, $data, $mapping, $defaults);
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntity($class, array $data)
    {
        $object = $this->doctrine->getRepository($class)->findOneBy(array('code' => $data['code']));
        if (!$object) {
            $object = $this->create($class);
        }

        return $object;
    }

    /**
     * Creates an entity of the given class
     *
     * @param  string $class
     * @return object
     */
    protected function create($class)
    {
        return new $class;
    }
}
