<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Publisher\Product;

use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Exception\ProductHasNoIdentifierException;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Publisher\PublisherInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\PublishedProductRepositoryInterface;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

/**
 * Product publisher.
 * At this step, completenesses of the published product are not calculated.
 * They will be calculated once the published will be saved.
 * This is automatically done via the {@link Akeneo\Pim\WorkOrganization\Workflow\Bundle\Manager\PublishedProductManager}
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class ProductPublisher implements PublisherInterface
{
    public function __construct(
        protected ProductBuilderInterface $productBuilder,
        protected PublisherInterface $publisher,
        protected RelatedAssociationPublisher $associationPublisher,
        protected VersionManager $versionManager,
        protected NormalizerInterface $normalizer,
        protected ObjectUpdaterInterface $productUpdater,
        protected PublishedProductRepositoryInterface $publishedProductRepository,
        private TranslatorInterface $translator
    ) {
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

        if (null === $object->getIdentifier()) {
            throw new ProductHasNoIdentifierException();
        }

        $options = array_merge(['with_associations' => true], $options);
        $standardProduct = $this->normalizer->normalize($object, 'standard');
        unset($standardProduct['associations']);
        // TODO: will be doable once PIM-6564 done
        unset($standardProduct['parent']);

        $familyCode = null !== $object->getFamily() ? $object->getFamily()->getCode() : null;

        //PIM-10285: this prevents from trying to publish a product already published by a concurrent process
        if (null !== $this->publishedProductRepository->findOneByOriginalProduct($object)) {
            throw new \LogicException(
                $this->translator->trans(
                    'pimee_enrich.mass_edit.product.operation.publish.already_published_product',
                    ['{{ productId }}' => $object->getIdentifier()],
                    'jsmessages'
                )
            );
        }
        $publishedProduct = $this->productBuilder->createProduct($object->getIdentifier(), $familyCode);
        Assert::implementsInterface($publishedProduct, PublishedProductInterface::class);
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
