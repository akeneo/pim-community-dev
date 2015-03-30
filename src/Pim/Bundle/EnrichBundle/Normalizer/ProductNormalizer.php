<?php

namespace Pim\Bundle\EnrichBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\TransformBundle\Normalizer\Filter\FilterableNormalizerInterface;
use Pim\Bundle\VersioningBundle\Manager\VersionManager;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Product normalizer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNormalizer implements NormalizerInterface, SerializerAwareInterface, FilterableNormalizerInterface
{
    /** @var array */
    protected $supportedFormat = ['internal_api'];

    /** @var NormalizerInterface */
    protected $productNormalizer;

    /** @var VersionManager */
    protected $versionManager;

    /** @var SerializerInterface */
    protected $serializer;

    /**
     * @param NormalizerInterface $productNormalizer
     * @param VersionManager      $versionManager
     */
    public function __construct(NormalizerInterface $productNormalizer, VersionManager $versionManager)
    {
        $this->productNormalizer = $productNormalizer;
        $this->versionManager    = $versionManager;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = array())
    {
        $normalizedProduct = $this->productNormalizer->normalize($product, 'json', $context);

        $normalizedProduct['meta'] = [
            'id'      => $product->getId(),
            'label'   => $product->getLabel(),
            'created' => $this->serializer->normalize(
                $this->versionManager->getOldestLogEntry($product),
                'array'
            ),
            'updated' => $this->serializer->normalize(
                $this->versionManager->getNewestLogEntry($product),
                'array'
            )
        ] + $this->getAssociationMeta($product);

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

    /**
     * @param ProductInterface $product
     *
     * @return array
     */
    protected function getAssociationMeta(ProductInterface $product)
    {
        $meta = [];
        $associations = $product->getAssociations();

        foreach ($associations as $association) {
            $associationType = $association->getAssociationType();
            $meta[$associationType->getCode()]['groupIds'] = array_map(
                function ($group) {
                    return $group->getId();
                },
                $association->getGroups()->toArray()
            );
        }

        return ['associations' => $meta];
    }
}
