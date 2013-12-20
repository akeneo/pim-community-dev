<?php

namespace Pim\Bundle\ImportExportBundle\Transformer;

/**
 * Transformer for job instances
 * 
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ORMJobInstanceTransformer extends ORMTransformer
{
    /**
     * {@inheritdoc}
     */
    protected function findEntity($class, array $data)
    {
        return $this->doctrine->getRepository($class)->findOneByCode($data['code']);
    }

    /**
     * {@inheritdoc}
     */
    protected function createEntity($class, array $data)
    {
        return new $class(null, null, null);
    }
}
