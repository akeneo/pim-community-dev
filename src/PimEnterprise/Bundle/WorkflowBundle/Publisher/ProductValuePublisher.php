<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Publisher;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Product value publisher
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductValuePublisher implements PublisherInterface
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
        $publishedValue = new $this->publishClassName();
        $publishedValue->setAttribute($object->getAttribute());
        $publishedValue->setLocale($object->getLocale());
        $publishedValue->setScope($object->getScope());

        $originalData = $object->getData();
        $copiedData = null;
        $options = ['product' => $object->getEntity(), 'value' => $object];

        if ($originalData instanceof \Doctrine\Common\Collections\Collection) {
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

        if ($copiedData) {
            $publishedValue->setData($copiedData);
        }

        return $publishedValue;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof ProductValueInterface;
    }
}
