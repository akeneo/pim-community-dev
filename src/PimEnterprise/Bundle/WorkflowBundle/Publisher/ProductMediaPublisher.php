<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Publisher;

use Pim\Bundle\CatalogBundle\Model\AbstractMedia;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;

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

    /** @var MediaManager */
    protected $mediaManager;

    const PREFIX_FILE = 'published';

    /**
     * @param string       $publishClassName
     * @param MediaManager $mediaManager
     */
    public function __construct($publishClassName, MediaManager $mediaManager)
    {
        $this->publishClassName = $publishClassName;
        $this->mediaManager     = $mediaManager;
    }

    /**
     * {@inheritdoc}
     */
    public function publish($object, array $options = [])
    {
        $copiedMedia = new $this->publishClassName();
        $this->mediaManager->duplicate($object, $copiedMedia, self::PREFIX_FILE);

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
