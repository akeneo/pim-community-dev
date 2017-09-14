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
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\Workflow\Model\PublishedProductInterface;
use PimEnterprise\Component\Workflow\Publisher\PublisherInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product publisher.
 * At this step, completenesses of the published product are not calculated.
 * They will be calculated once the published will be saved.
 * This is automatically done via the {@link PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager}
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class ProductPublisher implements PublisherInterface
{
    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var PublisherInterface */
    protected $publisher;

    /** @var RelatedAssociationPublisher */
    protected $associationPublisher;

    /** @var VersionManager */
    protected $versionManager;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ObjectUpdaterInterface */
    protected $productUpdater;

    /**
     * @param ProductBuilderInterface     $productBuilder
     * @param PublisherInterface          $publisher
     * @param RelatedAssociationPublisher $associationPublisher
     * @param VersionManager              $versionManager
     * @param NormalizerInterface         $normalizer
     * @param ObjectUpdaterInterface      $productUpdater
     */
    public function __construct(
        ProductBuilderInterface $productBuilder,
        PublisherInterface $publisher,
        RelatedAssociationPublisher $associationPublisher,
        VersionManager $versionManager,
        NormalizerInterface $normalizer,
        ObjectUpdaterInterface $productUpdater
    ) {
        $this->publisher = $publisher;
        $this->associationPublisher = $associationPublisher;
        $this->versionManager = $versionManager;
        $this->normalizer = $normalizer;
        $this->productUpdater = $productUpdater;
        $this->productBuilder = $productBuilder;
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
        // TODO: will be doable once PIM-6564 done
        unset($standardProduct['parent']);

        $familyCode = null !== $object->getFamily() ? $object->getFamily()->getCode() : null;
        $publishedProduct = $this->productBuilder->createProduct($object->getIdentifier(), $familyCode);
        $this->productUpdater->update($publishedProduct, $standardProduct);
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
}
