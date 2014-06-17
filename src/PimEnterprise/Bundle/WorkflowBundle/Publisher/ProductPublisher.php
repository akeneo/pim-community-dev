<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Publisher;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface;

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
        $this->copyValues($object, $published);

        return $published;
    }

    /**
     * Copy the family
     *
     * @param ProductInterface          $product
     * @param PublishedProductInterface $published
     */
    protected function copyFamily(ProductInterface $product, PublishedProductInterface $published)
    {
        $published->setFamily($product->getFamily());
    }

    /**
     * Copy the groups
     *
     * @param ProductInterface          $product
     * @param PublishedProductInterface $published
     */
    protected function copyGroups(ProductInterface $product, PublishedProductInterface $published)
    {
        foreach ($product->getGroups() as $group) {
            $published->addGroup($group);
        }
    }

    /**
     * Copy the categories
     *
     * @param ProductInterface          $product
     * @param PublishedProductInterface $published
     */
    protected function copyCategories(ProductInterface $product, PublishedProductInterface $published)
    {
        foreach ($product->getCategories() as $category) {
            $published->addCategory($category);
        }
    }

    /**
     * Copy the associations
     *
     * @param ProductInterface          $product
     * @param PublishedProductInterface $published
     */
    protected function copyAssociations(ProductInterface $product, PublishedProductInterface $published)
    {
        foreach ($product->getAssociations() as $association) {
            $copiedAssociation = $this->publisher->publish($association, ['published' => $published]);
            if (count($copiedAssociation->getGroups()) > 0 || count($copiedAssociation->getProducts())) {
                $published->addAssociation($copiedAssociation);
            }
        }
    }

    /**
     * Copy the completeness
     *
     * @param ProductInterface          $product
     * @param PublishedProductInterface $published
     */
    protected function copyCompletenesses(ProductInterface $product, PublishedProductInterface $published)
    {
        $copiedData = new ArrayCollection();
        foreach ($product->getCompletenesses() as $completeness) {
            $copiedCompleteness = $this->publisher->publish($completeness, ['published' => $published]);
            $copiedData->add($copiedCompleteness);
        }
        $published->setCompletenesses($copiedData);
    }

    /**
     * Copy the product values
     *
     * @param ProductInterface          $product
     * @param PublishedProductInterface $published
     */
    protected function copyValues(ProductInterface $product, PublishedProductInterface $published)
    {
        foreach ($product->getValues() as $originalValue) {
            $publishedValue = $this->publisher->publish($originalValue);
            $published->addValue($publishedValue);
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
