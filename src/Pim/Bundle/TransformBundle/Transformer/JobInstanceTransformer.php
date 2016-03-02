<?php

namespace Pim\Bundle\TransformBundle\Transformer;

/**
 * Transformer for job instances
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be removed in 1.6
 */
class JobInstanceTransformer extends EntityTransformer
{
    /**
     * {@inheritdoc}
     */
    protected function findEntity($class, array $data)
    {
        return $this->doctrine->getRepository($class)->findOneByCode($data['code']);
    }
}
