<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Bundle\WorkflowBundle\Applier\ProductDraftApplierInterface;
use PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraftInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Product normalizer
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ProductNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var PublishedProductManager */
    protected $publishedManager;

    /** @var ProductDraftRepositoryInterface */
    protected $draftRepository;

    /** @var ProductDraftApplierInterface */
    protected $draftApplier;

    /** @var CategoryAccessRepository */
    protected $categoryAccessRepo;

    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var SerializerInterface */
    protected $serializer;

    /**
     * @param NormalizerInterface             $normalizer
     * @param PublishedProductManager         $publishedManager
     * @param ProductDraftRepositoryInterface $draftRepository
     * @param ProductDraftApplierInterface    $draftApplier
     * @param CategoryAccessRepository        $categoryAccessRepo
     * @param SecurityContextInterface        $securityContext
     */
    public function __construct(
        NormalizerInterface $normalizer,
        PublishedProductManager $publishedManager,
        ProductDraftRepositoryInterface $draftRepository,
        ProductDraftApplierInterface $draftApplier,
        CategoryAccessRepository $categoryAccessRepo,
        SecurityContextInterface $securityContext
    ) {
        $this->normalizer         = $normalizer;
        $this->publishedManager   = $publishedManager;
        $this->draftRepository    = $draftRepository;
        $this->draftApplier       = $draftApplier;
        $this->categoryAccessRepo = $categoryAccessRepo;
        $this->securityContext    = $securityContext;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = [])
    {
        if (!$this->securityContext->isGranted(Attributes::OWN, $product) &&
            null !== $draft = $this->findDraftForProduct($product)
        ) {
            $workingCopy = $this->normalizer->normalize($product, 'json', $context);
            $draftStatus = $draft->getStatus();
            $this->draftApplier->apply($product, $draft);
            $normalizedProduct = $this->normalizer->normalize($product, 'internal_api', $context);
        } else {
            $workingCopy = null;
            $draftStatus = null;
            $normalizedProduct = $this->normalizer->normalize($product, 'internal_api', $context);
        }

        $published   = $this->publishedManager->findPublishedProductByOriginalId($product->getId());
        $ownerGroups = $this->categoryAccessRepo->getGrantedUserGroupsForProduct(
            $product,
            Attributes::OWN_PRODUCTS
        );

        $normalizedProduct['meta'] = array_merge(
            $normalizedProduct['meta'],
            [
                'published' => $published ?
                    $this->serializer->normalize($published->getVersion(), 'internal_api', $context) :
                    null,
                'owner_groups' => $this->serializer->normalize($ownerGroups, 'internal_api', $context),
                'working_copy' => $workingCopy,
                'draft_status' => $draftStatus
            ]
        );

        return $normalizedProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $this->normalizer->supportsNormalization($data, $format);
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Find a product draft for the specified product
     *
     * @param ProductInterface $product
     *
     * @return ProductDraftInterface|null
     */
    protected function findDraftForProduct(ProductInterface $product)
    {
        return $this->draftRepository->findUserProductDraft($product, $this->getUsername());
    }

    /**
     * Return the current username
     *
     * @return string
     */
    protected function getUsername()
    {
        return $this->securityContext->getToken()->getUsername();
    }
}
