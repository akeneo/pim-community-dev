<?php

namespace Pim\Bundle\EnrichBundle\Form\Handler;

/**
 * Handler interface for an entity, use to bind form, validate and save entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface HandlerInterface
{
    /**
     * Process method for handler
     *
     * @param mixed $entity
     *
     * @return boolean
     */
    public function process($entity);
}
