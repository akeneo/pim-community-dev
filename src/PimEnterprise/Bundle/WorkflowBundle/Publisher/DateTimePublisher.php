<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Publisher;

/**
 * Datetime publisher
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class DateTimePublisher implements PublisherInterface
{
    /**
     * {@inheritdoc}
     */
    public function publish($object, array $options = [])
    {
        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof \DateTime;
    }
}
