<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Workflow\Publisher\Product;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Factory\ProductValueFactory;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
use PimEnterprise\Component\Workflow\Model\PublishedProductValueInterface;
use PimEnterprise\Component\Workflow\Publisher\PublisherInterface;

/**
 * Product value publisher
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class ValuePublisher implements PublisherInterface
{
    /** @var string */
    protected $publishClassName;

    /** @var PublisherInterface */
    protected $publisher;

    /**
     * @param string             $publishClassName
     * @param PublisherInterface $publisher
     */
    public function __construct($publishClassName, PublisherInterface $publisher)
    {
        $this->publishClassName = $publishClassName;
        $this->publisher = $publisher;
    }

    /**
     * {@inheritdoc}
     */
    public function publish($object, array $options = [])
    {
        $originalData = $object->getData();
        $copiedData = null;

        $options['value'] = $object;

        if ($originalData instanceof Collection) {
            if (count($originalData) > 0) {
                $copiedData = new ArrayCollection();
                foreach ($originalData as $object) {
                    $copiedObject = $this->publisher->publish($object, $options);
                    $copiedData->add($copiedObject);
                }
            }
        } elseif (is_object($originalData)) {
            $copiedData = $this->publisher->publish($originalData, $options);
        } else {
            $copiedData = $originalData;
        }

        $publishedValue = $this->createNewPublishedProductValue(
            $object->getAttribute(),
            $object->getScope(),
            $object->getLocale(),
            $copiedData
        );

        return $publishedValue;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof ProductValueInterface;
    }

    /**
     * @todo TIP-692: This should used a PublishedProductValueFactory.
     *
     * @param AttributeInterface $attribute
     * @param string             $channel
     * @param string             $locale
     * @param string|null        $data
     *
     * @return PublishedProductValueInterface
     */
    protected function createNewPublishedProductValue(AttributeInterface $attribute, $channel, $locale, $data)
    {
        return new $this->publishClassName($attribute, $channel, $locale, $data);
    }
}
