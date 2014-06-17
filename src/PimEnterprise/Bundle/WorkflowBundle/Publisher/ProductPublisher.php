<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Publisher;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\Media;
use Pim\Bundle\CatalogBundle\Model\Metric;
use Pim\Bundle\CatalogBundle\Model\ProductPrice;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedProductRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProduct;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductValue;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductMedia;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductPrice;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductMetric;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductAssociation;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductCompleteness;

/**
 * Product publisher
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class ProductPublisher implements PublisherInterface
{
    /** @var string */
    protected $publishClassName;

    /** @var PublisherInterface */
    protected $publisher;

    /** @var PublishedProductRepositoryInterface*/
    protected $repository;

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
        $published = new $this->publishClassName();
        $published->setOriginalProductId($object->getId());
        $this->copyFamily($object, $published);
        $this->copyGroups($object, $published);
        $this->copyCategories($object, $published);
        $this->copyAssociations($object, $published);
        $this->copyCompletenesses($object, $published);
        //$this->copyValues($object, $published);
        return $published;
    }

    /**
     * @param ProductInterface $product
     * @param PublishedProduct $published
     */
    protected function copyFamily(ProductInterface $product, PublishedProduct $published)
    {
        $published->setFamily($product->getFamily());
    }

    /**
     * @param ProductInterface $product
     * @param PublishedProduct $published
     */
    protected function copyGroups(ProductInterface $product, PublishedProduct $published)
    {
        foreach ($product->getGroups() as $group) {
            $published->addGroup($group);
        }
    }

    /**
     * @param ProductInterface $product
     * @param PublishedProduct $published
     */
    protected function copyCategories(ProductInterface $product, PublishedProduct $published)
    {
        foreach ($product->getCategories() as $category) {
            $published->addCategory($category);
        }
    }

    /**
     * @param ProductInterface $product
     * @param PublishedProduct $published
     */
    protected function copyAssociations(ProductInterface $product, PublishedProduct $published)
    {
        foreach ($product->getAssociations() as $association) {
            $copiedAssociation = $this->publisher->publish($association, ['published' => $published]);
            if (count($copiedAssociation->getGroups()) > 0 || count($copiedAssociation->getProducts())) {
                $published->addAssociation($copiedAssociation);
            }
        }
    }

    /**
     * @param ProductInterface $product
     * @param PublishedProduct $published
     */
    protected function copyCompletenesses(ProductInterface $product, PublishedProduct $published)
    {
        $copiedData = new ArrayCollection();
        foreach ($product->getCompletenesses() as $completeness) {
            $copiedCompleteness = $this->publisher->publish($completeness, ['published' => $published]);
            $copiedData->add($copiedCompleteness);
        }
        $published->setCompletenesses($copiedData);
    }

    /**
     * TODO : ugly POC method, we'll use normalization + processing to deal with the copy
     *
     * @param ProductInterface $product
     * @param PublishedProduct $published
     */
    protected function copyValues(ProductInterface $product, PublishedProduct $published)
    {
        foreach ($product->getValues() as $originalValue) {

            // publish ?

            $publishedValue = new PublishedProductValue();
            $publishedValue->setAttribute($originalValue->getAttribute());
            $publishedValue->setLocale($originalValue->getLocale());
            $publishedValue->setScope($originalValue->getScope());

            $originalData = $originalValue->getData();
            $copiedData = null;

            //$copiedData = $this->publish($originalData);

            // collection -> call publish
            // - price
            // - option
            // metric
            // media
            // raw

            if ($originalData instanceof \Doctrine\Common\Collections\Collection) {
                if (count($originalData) > 0) {
                    $copiedData = new ArrayCollection();
                    foreach ($originalData as $object) {
                        if ($object instanceof ProductPrice) {
                            $copiedObject = new PublishedProductPrice();
                            $copiedObject->setData($object->getData());
                            $copiedObject->setCurrency($object->getCurrency());
                            $copiedData->add($copiedObject);
                        } elseif ($object instanceof AttributeOption) {
                            $copiedData->add($object);
                        }
                    }
                }

            } elseif (is_object($originalData) && $originalData instanceof Metric) {

                $copiedMetric = new PublishedProductMetric();
                $copiedMetric->setData($originalData->getData());
                $copiedMetric->setBaseData($originalData->getBaseData());
                $copiedMetric->setUnit($originalData->getUnit());
                $copiedMetric->setBaseUnit($originalData->getBaseUnit());
                $copiedMetric->setFamily($originalData->getFamily());
                $copiedData = $copiedMetric;

            } elseif (is_object($originalData) && $originalData instanceof Media) {
                // TODO : we have to copy the media file not reference the same !
                $copiedMedia = new PublishedProductMedia();
                $copiedMedia->setFilename($originalData->getFilename());
                $copiedMedia->setOriginalFilename($originalData->getOriginalFilename());
                $copiedMedia->setFilePath($originalData->getFilePath());
                $copiedMedia->setMimeType($originalData->getMimeType());
                $copiedData = $copiedMedia;

            } else {
                $copiedData = $originalData;
            }

            if ($copiedData) {
                $publishedValue->setData($copiedData);
                $published->addValue($publishedValue);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof ProductInterface;
    }
}
