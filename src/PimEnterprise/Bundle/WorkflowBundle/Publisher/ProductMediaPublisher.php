<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Publisher;

use Pim\Bundle\CatalogBundle\Model\AbstractMedia;

/**
 * Product media publisher
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductMediaPublisher implements PublisherInterface
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
        $copiedMedia = new $this->publishClassName();
        $copiedMedia->setFilename($object->getFilename());
        $copiedMedia->setOriginalFilename($object->getOriginalFilename());
        // TODO :copy the media !!
        $copiedMedia->setFilePath($object->getFilePath());
        $copiedMedia->setMimeType($object->getMimeType());

        return $copiedMedia;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof AbstractMedia;
    }
}
