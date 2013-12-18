<?php

namespace Pim\Bundle\ImportExportBundle\Transformer;

use Pim\Bundle\ImportExportBundle\Exception\MissingIdentifierException;

/**
 * Transforms an array in an entity
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ORMTransformer extends AbstractORMTransformer
{
    /**
     * Transforms an array into an entity
     *
     * @param string $class
     * @param array  $data
     * @param array  $defaults
     *
     * @return object
     */
    public function transform($class, array $data, array $defaults = array())
    {
        return $this->doTransform($class, $data, $defaults);
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntity($class, array $data)
    {
        $repository = $this->doctrine->getRepository($class);
        if (count(array_diff($repository->getReferenceProperties(), array_keys($data)))) {
            throw new MissingIdentifierException();
        }
        $refProperties = $repository->getReferenceProperties();
        $reference = implode(
            '.',
            array_map(
                function ($property) use($data) {
                    if (!isset($data[$property])) {
                        throw new MissingIdentifierException;
                    }

                    return $data[$property];
                },
                $refProperties
            )
        );

        $object = $this->doctrine->getRepository($class)->findByReference($reference);
        if (!$object) {
            $object = $this->create($class);
        }

        return $object;
    }

    /**
     * Creates an entity of the given class
     *
     * @param string $class
     *
     * @return object
     */
    protected function create($class)
    {
        return new $class;
    }
}
