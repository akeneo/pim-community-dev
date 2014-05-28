<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Factory;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\Media;
use Pim\Bundle\CatalogBundle\Model\Metric;
use Pim\Bundle\CatalogBundle\Model\ProductPrice;
use Pim\Bundle\CatalogBundle\Entity\AttributeOption;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProduct;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductValue;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductMedia;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductPrice;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductMetric;

/**
 * Published product factory
 * TODO : a POC class, naive implementation of publish
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PublishedProductFactory
{
    /**
     * Create/update a published product instance
     *
     * @param ProductInterface $product
     *
     * @return PublishedProduct
     */
    public function createPublishedProduct(ProductInterface $product)
    {
        $published = new PublishedProduct();
        $this->copyValues($product, $published);
        $this->copyFamily($product, $published);
        $this->copyGroups($product, $published);
        $this->copyCategories($product, $published);
        $this->copyAssociations($product, $published);

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
        // foreach ($product->getAssociations() as $association) {
        // $published->addAssociation($copiedAssociation);
        // }
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
            $publishedValue = new PublishedProductValue();
            $publishedValue->setAttribute($originalValue->getAttribute());
            $publishedValue->setLocale($originalValue->getLocale());
            $publishedValue->setScope($originalValue->getScope());

            $originalData = $originalValue->getData();
            $copiedData = null;

            if ($originalData instanceof \Doctrine\Common\Collections\Collection) {
                if (count($originalData) > 0) {
                    $copiedData = [];
                    foreach ($originalData as $object) {
                        if ($object instanceof ProductPrice) {
                            $copiedObject = new PublishedProductPrice();
                            $copiedObject->setData($object->getData());
                            $copiedObject->setCurrency($object->getCurrency());
                            $copiedData[]= $copiedObject;
                        } elseif ($object instanceof AttributeOption) {
                            $copiedData[]= $object;
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
}
