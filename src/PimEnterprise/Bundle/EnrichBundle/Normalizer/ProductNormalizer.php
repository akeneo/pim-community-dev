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
use PimEnterprise\Bundle\WorkflowBundle\Manager\ProductDraftManager;
use PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager;
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

    /** @var ProductDraftManager */
    protected $draftManager;

    /** @var ProductDraftApplierInterface */
    protected $draftApplier;

    /** @var CategoryAccessRepository */
    protected $categoryAccessRepo;

    /** @var SerializerInterface */
    protected $serializer;

    /**
     * @param NormalizerInterface          $normalizer
     * @param PublishedProductManager      $publishedManager
     * @param ProductDraftManager          $draftManager
     * @param ProductDraftApplierInterface $draftApplier
     * @param CategoryAccessRepository     $categoryAccessRepo
     */
    public function __construct(
        NormalizerInterface $normalizer,
        PublishedProductManager $publishedManager,
        ProductDraftManager $draftManager,
        ProductDraftApplierInterface $draftApplier,
        CategoryAccessRepository $categoryAccessRepo
    ) {
        $this->normalizer         = $normalizer;
        $this->publishedManager   = $publishedManager;
        $this->draftManager       = $draftManager;
        $this->draftApplier       = $draftApplier;
        $this->categoryAccessRepo = $categoryAccessRepo;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = [])
    {
        $workingCopy = $this->normalizer->normalize($product, 'json', $context);
        $published   = $this->publishedManager->findPublishedProductByOriginalId($product->getId());
        $ownerGroups = $this->categoryAccessRepo->getGrantedUserGroupsForProduct(
            $product,
            Attributes::OWN_PRODUCTS
        );

        $draft = $this->draftManager->findOrCreate($product);
        $this->draftApplier->apply($product, $draft);
        $modifiedProduct = $this->normalizer->normalize($product, 'internal_api', $context);

        $modifiedProduct['meta'] = array_merge(
            $modifiedProduct['meta'],
            [
                'published' => $published ?
                    $this->serializer->normalize($published->getVersion(), 'internal_api', $context) :
                    null,
                'owner_groups' => $this->serializer->normalize($ownerGroups, 'internal_api', $context),
                'working_copy' => $workingCopy,
                'draft_status' => $this->serializer->normalize($draft->getStatus(), 'internal_api', $context)
            ]
        );

        return $modifiedProduct;
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
}
