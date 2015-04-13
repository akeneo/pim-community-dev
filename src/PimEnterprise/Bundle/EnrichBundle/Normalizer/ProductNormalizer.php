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

use PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\TransformBundle\Normalizer\Filter\FilterableNormalizerInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Product normalizer
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ProductNormalizer implements NormalizerInterface, SerializerAwareInterface, FilterableNormalizerInterface
{
    /** @var array */
    protected $supportedFormat = ['internal_api'];

    /** @var NormalizerInterface */
    protected $productNormalizer;

    /** @var PublishedProductManager */
    protected $manager;

    /** @var SerializerInterface */
    protected $serializer;

    /**
     * @param NormalizerInterface     $productNormalizer
     * @param PublishedProductManager $manager
     */
    public function __construct(
        NormalizerInterface $productNormalizer,
        PublishedProductManager $manager
    ) {
        $this->productNormalizer = $productNormalizer;
        $this->manager           = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = array())
    {
        $normalizedProduct = $this->productNormalizer->normalize($product, 'internal_api', $context);
        $published = $this->manager->findPublishedProductByOriginalId($product->getId());

        $normalizedProduct['meta'] = array_merge(
            $normalizedProduct['meta'],
            [
                'published' => $published ?
                    $this->serializer->normalize($published->getVersion(), 'json', $context) :
                    null
            ]
        );

        return $normalizedProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function setFilters(array $filters)
    {
        $this->productNormalizer->setFilters($filters);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface && in_array($format, $this->supportedFormat);
    }

    /**
     * {@inheritdoc}
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }
}
