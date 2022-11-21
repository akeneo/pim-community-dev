<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard\Product;

use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Transform the properties of a product object (fields and product values)
 * to a standardized array
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PropertiesNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    public const FIELD_UUID = 'uuid';
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_LABEL = 'label';
    public const FIELD_FAMILY = 'family';
    public const FIELD_PARENT = 'parent';
    public const FIELD_GROUPS = 'groups';
    public const FIELD_CATEGORIES = 'categories';
    public const FIELD_ENABLED = 'enabled';
    public const FIELD_VALUES = 'values';
    public const FIELD_CREATED = 'created';
    public const FIELD_UPDATED = 'updated';

    public function __construct(private CollectionFilterInterface $filter, private NormalizerInterface $normalizer)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = [])
    {
        $context = array_merge(['filter_types' => ['pim.transform.product_value.structured']], $context);
        $data = [];

        // TODO TIP-987 Remove this when decoupling PublishedProduct from Enrichment
        if (\get_class($product) !== 'Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProduct') {
            $data[self::FIELD_UUID] = $product->getUuid()->toString();
        }
        $data[self::FIELD_IDENTIFIER] = $product->getIdentifier();
        $data[self::FIELD_FAMILY] = $product->getFamily() ? $product->getFamily()->getCode() : null;
        if ($product->isVariant() && null !== $product->getParent()) {
            $data[self::FIELD_PARENT] = $product->getParent()->getCode();
        } else {
            $data[self::FIELD_PARENT] = null;
        }
        $data[self::FIELD_GROUPS] = $product->getGroupCodes();
        $data[self::FIELD_CATEGORIES] = $product->getCategoryCodes();
        $data[self::FIELD_ENABLED] = (bool) $product->isEnabled();
        $data[self::FIELD_VALUES] = $this->normalizeValues($product->getValues(), $format, $context);
        $data[self::FIELD_CREATED] = $this->normalizer->normalize($product->getCreated(), $format);
        $data[self::FIELD_UPDATED] = $this->normalizer->normalize($product->getUpdated(), $format);

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ProductInterface && 'standard' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * Normalize the values of the product
     *
     * @param WriteValueCollection $values
     * @param string                   $format
     * @param array                    $context
     *
     * @return ArrayCollection
     */
    private function normalizeValues(WriteValueCollection $values, $format, array $context = [])
    {
        foreach ($context['filter_types'] as $filterType) {
            $values = $this->filter->filterCollection($values, $filterType, $context);
        }

        return $this->normalizer->normalize($values, $format, $context);
    }
}
