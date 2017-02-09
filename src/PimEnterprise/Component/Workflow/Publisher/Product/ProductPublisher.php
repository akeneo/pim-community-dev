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

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Manager\CompletenessManager;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Workflow\Model\PublishedProductInterface;
use PimEnterprise\Component\Workflow\Publisher\PublisherInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

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

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ObjectUpdaterInterface */
    protected $productUpdater;

    /**
     * @param string                      $publishClassName
     * @param PublisherInterface          $publisher
     * @param RelatedAssociationPublisher $associationPublisher
     * @param VersionManager              $versionManager
     * @param CompletenessManager         $completenessManager
     * @param NormalizerInterface         $normalizer
     * @param ObjectUpdaterInterface      $productUpdater
     */
    public function __construct(
        $publishClassName,
        PublisherInterface $publisher,
        RelatedAssociationPublisher $associationPublisher,
        VersionManager $versionManager,
        CompletenessManager $completenessManager,
        NormalizerInterface $normalizer,
        ObjectUpdaterInterface $productUpdater
    ) {
        $this->publishClassName = $publishClassName;
        $this->publisher = $publisher;
        $this->associationPublisher = $associationPublisher;
        $this->versionManager = $versionManager;
        $this->completenessManager = $completenessManager;
        $this->normalizer = $normalizer;
        $this->productUpdater = $productUpdater;
    }

    /**
     * {@inheritdoc}
     */
    public function publish($object, array $options = [])
    {
        if (!$object instanceof ProductInterface) {
            throw new \InvalidArgumentException(
                sprintf('The object to be published is not a product, "%s" given', ClassUtils::getClass($object))
            );
        }
        $options = array_merge(['with_associations' => true], $options);
        $standardProduct = $this->normalizer->normalize($object, 'standard');
        unset($standardProduct['associations']);

        $publishedProduct = $this->createNewPublishedProduct();
        $this->productUpdater->update($publishedProduct, $standardProduct);

        // TODO: to activate when completeness works (TIP-694)
        // $this->completenessManager->generateMissingForProduct($object);
        // $this->copyCompletenesses($object, $published);

        $publishedProduct->setIdentifier($object->getIdentifier());
        $publishedProduct->setOriginalProduct($object);
        $this->setVersion($object, $publishedProduct);

        if (true === $options['with_associations']) {
            $this->copyAssociations($object, $publishedProduct);
            $this->updateRelatedAssociations($publishedProduct);
        }

        return $publishedProduct;
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
