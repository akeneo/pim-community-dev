<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Publisher\Product;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Publisher\PublisherInterface;

/**
 * Product publisher
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class ProductPublisher implements PublisherInterface
{
    /** @var string */
    protected $publishClassName;

    /** @var PublisherInterface */
    protected $publisher;

    /** @var RelatedAssociationPublisher */
    protected $associationPublisher;

    /** @var VersionManager */
    protected $versionManager;

    /** @var CompletenessManager */
    protected $completenessManager;

    /**
     * @param string                      $publishClassName
     * @param PublisherInterface          $publisher
     * @param RelatedAssociationPublisher $associationPublisher
     * @param VersionManager              $versionManager
     * @param CompletenessManager         $completenessManager
     */
    public function __construct(
        $publishClassName,
        PublisherInterface $publisher,
        RelatedAssociationPublisher $associationPublisher,
        VersionManager $versionManager,
        CompletenessManager $completenessManager
    ) {
        $this->publishClassName = $publishClassName;
        $this->publisher = $publisher;
        $this->associationPublisher = $associationPublisher;
        $this->versionManager = $versionManager;
        $this->completenessManager = $completenessManager;
    }

    /**
     * {@inheritdoc}
     */
    public function publish($object, array $options = [])
    {
        $options = array_merge(['with_associations' => true], $options);
        $this->completenessManager->generateMissingForProduct($object);
        $published = $this->createNewPublishedProduct();
        $published->setOriginalProduct($object);
        $published->setEnabled($object->isEnabled());
        $this->copyFamily($object, $published);
        $this->copyGroups($object, $published);
        $this->copyCategories($object, $published);
        $this->copyCompletenesses($object, $published);
        $this->copyValues($object, $published);
        $this->setVersion($object, $published);
        if (true === $options['with_associations']) {
            $this->copyAssociations($object, $published);
            $this->updateRelatedAssociations($published);
        }

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
        $this->associationPublisher->publish($published);
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

        if (!$version || $version->isPending()) {
            $createdVersions = $this->versionManager->buildVersion($product);
            foreach ($createdVersions as $createdVersion) {
                if ($createdVersion->getChangeset()) {
                    $this->versionManager->getObjectManager()->persist($createdVersion);
                    $version = $createdVersion;
                }
            }
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

    /**
     * @return PublishedProductInterface
     */
    protected function createNewPublishedProduct()
    {
        return new $this->publishClassName();
    }
}
