<?php

namespace Pim\Bundle\EnrichBundle\Form\Handler;

/**
 * Handler interface for an entity, used to bind the form, validate and save the entity
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
     * @return bool
     */
    public function process($entity);
}
