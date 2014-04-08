<?php

namespace Pim\Bundle\TransformBundle\Transformer;

/**
 * Transforms an array in an entity
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeOptionTransformer extends EntityTransformer
{
    /**
     * {@inheritdoc}
     */
    public function transform($class, array $data, array $defaults = array())
    {
        $entity = parent::transform($class, $data, $defaults);

        if ($entity->getAttribute() === null) {
            throw new \Exception(
                sprintf(
                    'The attribute used for option "%s" is not known',
                    $entity->getCode()
                )
            );
        }

        return $entity;
    }
}
