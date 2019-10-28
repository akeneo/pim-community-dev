<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\Standard;

use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    private const FIELD_CODE = 'code';
    private const FIELD_FAMILY_VARIANT = 'family_variant';
    private const FIELD_CATEGORIES = 'categories';
    private const FIELD_VALUES = 'values';
    private const FIELD_CREATED = 'created';
    private const FIELD_UPDATED = 'updated';
    private const FIELD_PARENT = 'parent';
    private const FIELD_ASSOCIATIONS = 'associations';

    /** @var NormalizerInterface */
    private $associationsNormalizer;

    /** @var NormalizerInterface*/
    private $standardNormalizer;

    /** @var CollectionFilterInterface */
    private $filter;

    /**
     * @param CollectionFilterInterface $filter The collection filter
     * @param NormalizerInterface       $associationsNormalizer
     * @param NormalizerInterface       $standardNormalizer
     */
    public function __construct(
        CollectionFilterInterface $filter,
        NormalizerInterface $associationsNormalizer,
        NormalizerInterface $standardNormalizer
    ) {
        $this->filter = $filter;
        $this->associationsNormalizer = $associationsNormalizer;
        $this->standardNormalizer = $standardNormalizer;
    }

    /**
     * {@inheritdoc}
     *
     * @param ProductModelInterface $productModel
     */
    public function normalize($productModel, $format = null, array $context = array()): array
    {
        $context = array_merge(['filter_types' => ['pim.transform.product_value.structured']], $context);

        $data[self::FIELD_CODE] = $productModel->getCode();
        $data[self::FIELD_FAMILY_VARIANT] = $productModel->getFamilyVariant()->getCode();
        $data[self::FIELD_PARENT] = null !== $productModel->getParent() ? $productModel->getParent()->getCode() : null;
        $data[self::FIELD_CATEGORIES] = $productModel->getCategoryCodes();
        $data[self::FIELD_VALUES] = $this->normalizeValues($productModel->getValues(), $format, $context);
        $data[self::FIELD_CREATED] = $this->standardNormalizer->normalize($productModel->getCreated(), $format, $context);
        $data[self::FIELD_UPDATED] = $this->standardNormalizer->normalize($productModel->getUpdated(), $format, $context);
        $data[self::FIELD_ASSOCIATIONS] = $this->associationsNormalizer->normalize($productModel, $format, $context);

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ProductModelInterface && 'standard' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * Normalize the values of the product model
     *
     * @param WriteValueCollection $values
     * @param string $format
     * @param array $context
     *
     * @return ArrayCollection
     */
    private function normalizeValues(WriteValueCollection $values, ?string $format, array $context = [])
    {
        foreach ($context['filter_types'] as $filterType) {
            $values = $this->filter->filterCollection($values, $filterType, $context);
        }

        $data = $this->standardNormalizer->normalize($values, $format, $context);

        return $data;
    }
}
