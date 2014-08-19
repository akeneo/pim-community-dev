<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Publisher\Product;

use Pim\Bundle\CatalogBundle\Model\AbstractCompleteness;
use PimEnterprise\Bundle\WorkflowBundle\Publisher\PublisherInterface;

/**
 * Product completeness publisher
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
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
        return $object instanceof AbstractCompleteness;
    }
}
