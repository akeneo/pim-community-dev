<?php
declare(strict_types=1);

namespace Pim\Component\Catalog\Normalizer\Standard;

use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelNormalizer extends SerializerAwareNormalizer implements NormalizerInterface
{
    private const FIELD_CODE = 'code';
    private const FIELD_FAMILY_VARIANT = 'family_variant';
    private const FIELD_CATEGORIES = 'categories';
    private const FIELD_VALUES = 'values';
    private const FIELD_CREATED = 'created';
    private const FIELD_UPDATED = 'updated';
    private const FIELD_PARENT = 'parent';

    /** @var CollectionFilterInterface */
    private $filter;

    /**
     * @param CollectionFilterInterface $filter The collection filter
     */
    public function __construct(CollectionFilterInterface $filter)
    {
        $this->filter = $filter;
    }

    /**
     * {@inheritdoc}
     *
     * @param ProductModelInterface $productModel
     */
    public function normalize($productModel, $format = null, array $context = array()): array
    {
        if (!$this->serializer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        $data[self::FIELD_CODE] = $productModel->getCode();
        $data[self::FIELD_FAMILY_VARIANT] = $productModel->getFamilyVariant()->getCode();
        $data[self::FIELD_PARENT] = null !== $productModel->getParent() ? $productModel->getParent()->getCode() : null;
        $data[self::FIELD_CATEGORIES] = $productModel->getCategoryCodes();
        $data[self::FIELD_VALUES] = $this->normalizeValues($productModel->getValues(), $format, $context);
        $data[self::FIELD_CREATED] = $this->serializer->normalize($productModel->getCreated(), $format, $context);
        $data[self::FIELD_UPDATED] = $this->serializer->normalize($productModel->getUpdated(), $format, $context);

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof ProductModelInterface && 'standard' === $format;
    }

    /**
     * Normalize the values of the product
     *
     * @param ValueCollectionInterface $values
     * @param string                   $format
     * @param array                    $context
     *
     * @return ArrayCollection
     */
    private function normalizeValues(ValueCollectionInterface $values, $format, array $context = [])
    {
        foreach ($context['filter_types'] as $filterType) {
            $values = $this->filter->filterCollection($values, $filterType, $context);
        }

        $data = $this->serializer->normalize($values, $format, $context);

        return $data;
    }
}
