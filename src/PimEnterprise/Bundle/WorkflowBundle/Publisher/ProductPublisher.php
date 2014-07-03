<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Publisher;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
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

    /** @var PublisherInterface */
    protected $relatedAssociationPublisher;

    /** @var VersionManager */
    protected $versionManager;

    /**
     * @param string             $publishClassName
     * @param PublisherInterface $publisher
     * @param PublisherInterface $relatedAssociationPublisher
     * @param VersionManager     $versionManager
     */
    public function __construct(
        $publishClassName,
        PublisherInterface $publisher,
        PublisherInterface $relatedAssociationPublisher,
        VersionManager $versionManager
    ) {
        $this->publishClassName = $publishClassName;
        $this->publisher = $publisher;
        $this->relatedAssociationPublisher = $relatedAssociationPublisher;
        $this->versionManager = $versionManager;
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
        $this->updateRelatedAssociations($published);
        $this->setVersion($object, $published);

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
            $published->addAssociation($copiedAssociation);
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
     * Publish related associations
     *
     * @param PublishedProductInterface $published
     */
    protected function updateRelatedAssociations(PublishedProductInterface $published)
    {
        $this->relatedAssociationPublisher->publish($published);
    }

    /**
     * Set the version of the published product
     *
     * @param ProductInterface          $product
     * @param PublishedProductInterface $published
     */
    protected function setVersion(ProductInterface $product, PublishedProductInterface $published)
    {
        $version = $this->versionManager->getNewestLogEntry($product, null);

        if ($version->isPending()) {
            $this->versionManager->buildPendingVersion($version);
        }

        $published->setVersion($version);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof ProductInterface;
    }
}
