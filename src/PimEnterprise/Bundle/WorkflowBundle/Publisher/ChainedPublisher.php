<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Publisher;

/**
 * Chained publisher, it knows other publishers and allow to publish an object by using the relevant publisher
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ChainedPublisher implements PublisherInterface
{
    /** @var PublisherInterface */
    protected $publishers;

    /**
     * @param PublisherInterface $publisher
     */
    public function addPublisher(PublisherInterface $publisher)
    {
        $this->publishers[]= $publisher;
    }

    /**
     * {@inheritdoc}
     */
    public function publish($object, array $options = [])
    {
        foreach ($this->publishers as $publisher) {
            if ($publisher->supports($object)) {
                return $publisher->publish($object, $options);
            }
        }

        throw new \LogicException(sprintf('Not able to publish the "%s" class', get_class($object)));
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return true;
    }
}
