<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Factory;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\Media;
use Pim\Bundle\CatalogBundle\Model\Metric;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProduct;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductValue;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductMedia;

/**
 * Published product factory
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

        $published->setFamily($product->getFamily());


/*        $proposal
            ->setProduct($product)
            ->setAuthor($username)
            ->setCreatedAt(new \DateTime())
            ->setChanges($changes);
*/

        return $published;
    }

    /**
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

/*
            echo $originalValue->getAttribute()->getCode().' : ';
            if (is_object($originalData)) {
                echo get_class($originalData);
            } elseif ($originalData instanceof \Doctrine\Common\Collections\Collection) {
                echo 'collection';
            } else {
                echo $originalData;
            }

            echo '<br/><br/>';
            continue;
*/

            if ($originalData instanceof \Doctrine\Common\Collections\Collection) {

                // price = clone !
                
                // option = copy


                echo 'collection';
                echo get_class($originalData);

//                $copiedData = $originalData;

                // price ?
            } elseif (is_object($originalData) && $originalData instanceof Metric) {

                // TODO : we have to copy the metric !
                continue;


            } elseif (is_object($originalData) && $originalData instanceof Media) {

                // TODO : we have to copy the media !
                continue;

                $copiedMedia = new PublishedProductMedia();
                $copiedMedia->setFilename($originalData->getFilename());
                $copiedMedia->setOriginalFilename($originalData->getOriginalFilename());
                $copiedMedia->setFilePath($originalData->getFilePath());
                $copiedMedia->setMimeType($originalData->getMimeType());
                $copiedData = $copiedMedia;

            } elseif (is_object($originalData)) {

                echo 'object';
                echo get_class($originalData);
                // TODO not deal with object for now
//                continue;

                $copiedData = $originalData;

            } else {

                echo 'rawdata';

                $copiedData = $originalData;
            }
            if ($copiedData) {
                $publishedValue->setData($copiedData);
                $published->addValue($publishedValue);
            } 
        }
    }
}
