<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Publisher;

/**
 * Chained publisher, it knows other publishers and allow to publish an object by using the relevant publisher
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class ChainedPublisher implements PublisherInterface
{
    /** @var PublisherInterface[] */
    protected $publishers;

    /**
     * @param PublisherInterface $publisher
     */
    public function addPublisher(PublisherInterface $publisher)
    {
        $this->publishers[] = $publisher;
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
