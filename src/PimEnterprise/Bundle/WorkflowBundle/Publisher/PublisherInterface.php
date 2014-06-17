<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Publisher;

/**
 * Publisher interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
interface PublisherInterface
{
    /**
     * Publish the source object
     *
     * @param object $object
     * @param array  $options
     *
     * @return object
     */
    public function publish($object, array $options = []);

    /**
     * Checks whether the given class is supported for publishing by this publisher
     *
     * @param object $object
     *
     * @return boolean
     */
    public function supports($object);
}
