<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Publisher;

/**
 * Unpublisher interface
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
interface UnpublisherInterface
{
    /**
     * Unpublish the source object
     *
     * @param object $object
     * @param array  $options
     */
    public function unpublish($object, array $options = []);

    /**
     * Checks whether the given class is supported for publishing by this publisher
     *
     * @param object $object
     *
     * @return boolean
     */
    public function supports($object);
}
