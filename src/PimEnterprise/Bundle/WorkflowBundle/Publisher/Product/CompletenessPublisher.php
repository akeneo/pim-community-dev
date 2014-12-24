<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Publisher\Product;

use Pim\Bundle\CatalogBundle\Model\CompletenessInterface;
use PimEnterprise\Bundle\WorkflowBundle\Publisher\PublisherInterface;

/**
 * Product completeness publisher
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class CompletenessPublisher implements PublisherInterface
{
    /** @var string */
    protected $publishClassName;

    /**
     * @param string $publishClassName
     */
    public function __construct($publishClassName)
    {
        $this->publishClassName = $publishClassName;
    }

    /**
     * {@inheritdoc}
     */
    public function publish($object, array $options = [])
    {
        if (!isset($options['published'])) {
            throw new \LogicException('Published product must be known');
        }
        $published = $options['published'];
        $copiedCompleteness = $this->createNewPublishedProductCompleteness();
        $copiedCompleteness->setLocale($object->getLocale());
        $copiedCompleteness->setChannel($object->getChannel());
        $copiedCompleteness->setProduct($published);
        $copiedCompleteness->setRatio($object->getRatio());
        $copiedCompleteness->setMissingCount($object->getMissingCount());
        $copiedCompleteness->setRequiredCount($object->getRequiredCount());

        return $copiedCompleteness;
    }

    /**
     * @return \PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductCompleteness
     */
    protected function createNewPublishedProductCompleteness()
    {
        return new $this->publishClassName();
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof CompletenessInterface;
    }
}
