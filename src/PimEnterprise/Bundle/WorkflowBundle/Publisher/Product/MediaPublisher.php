<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Publisher\Product;

use Pim\Bundle\CatalogBundle\Model\AbstractProductMedia;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use PimEnterprise\Bundle\WorkflowBundle\Publisher\PublisherInterface;

/**
 * Product media publisher
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class MediaPublisher implements PublisherInterface
{
    /** @var string */
    protected $publishClassName;

    /** @var MediaManager */
    protected $mediaManager;

    /** @staticvar string */
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
        if (!isset($options['product'])) {
            throw new \LogicException('Original product must be known');
        }
        if (!isset($options['value'])) {
            throw new \LogicException('Original product value must be known');
        }
        $product = $options['product'];
        $value = $options['value'];
        $copiedMedia = new $this->publishClassName();
        if ($value->getData() && $value->getData()->getFilePath()) {
            $prefix = sprintf(
                '%s-%s',
                self::PREFIX_FILE,
                $this->mediaManager->generateFilenamePrefix($product, $value)
            );
            $this->mediaManager->duplicate($object, $copiedMedia, $prefix);
        }

        return $copiedMedia;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof AbstractProductMedia;
    }
}
