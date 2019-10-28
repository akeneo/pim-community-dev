<?php

declare(strict_types=1);

namespace Oro\Bundle\PimDataGridBundle\Normalizer;

use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ImageNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\ImageAsLabel;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Query\VariantProductRatioInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product model normalizer for datagrid
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelNormalizer implements NormalizerInterface, NormalizerAwareInterface, CacheableSupportsMethodInterface
{
    use NormalizerAwareTrait;

    /** @var CollectionFilterInterface */
    private $filter;

    /** @var VariantProductRatioInterface */
    private $variantProductRatioQuery;

    /** @var ImageAsLabel */
    private $imageAsLabel;

    /** @var ImageNormalizer */
    private $imageNormalizer;

    /**
     * @param CollectionFilterInterface    $filter
     * @param VariantProductRatioInterface $variantProductRatioQuery
     * @param ImageAsLabel                 $imageAsLabel
     * @param ImageNormalizer              $imageNormalizer
     */
    public function __construct(
        CollectionFilterInterface $filter,
        VariantProductRatioInterface $variantProductRatioQuery,
        ImageAsLabel $imageAsLabel,
        ImageNormalizer $imageNormalizer
    ) {
        $this->filter                   = $filter;
        $this->variantProductRatioQuery = $variantProductRatioQuery;
        $this->imageAsLabel             = $imageAsLabel;
        $this->imageNormalizer          = $imageNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($productModel, $format = null, array $context = [])
    {
        if (!$this->normalizer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        $context = array_merge(['filter_types' => ['pim.transform.product_value.structured']], $context);
        $data = [];
        $locale = current($context['locales']);
        $channel = current($context['channels']);

        $variantProductCompleteness = $this->variantProductRatioQuery->findComplete($productModel);
        $closestImage = $this->imageAsLabel->value($productModel);

        $data['identifier'] = $productModel->getCode();
        $data['family'] = $this->getFamilyLabel($productModel, $locale);
        $data['values'] = $this->normalizeValues($productModel->getValues(), $format, $context);
        $data['created'] = $this->normalizer->normalize($productModel->getCreated(), $format, $context);
        $data['updated'] = $this->normalizer->normalize($productModel->getUpdated(), $format, $context);
        $data['label'] = $productModel->getLabel($locale, $channel);
        $data['image'] = $this->normalizeImage($closestImage, $context);
        $data['groups'] = null;
        $data['enabled'] = null;
        $data['completeness'] = null;
        $data['document_type'] = IdEncoder::PRODUCT_MODEL_TYPE;
        $data['technical_id'] = $productModel->getId();
        $data['search_id'] = IdEncoder::encode($data['document_type'], $data['technical_id']);
        $data['complete_variant_product'] = $variantProductCompleteness->value($channel, $locale);
        $data['is_checked'] = false;
        $data['parent'] = $this->getParentCode($productModel);

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductModelInterface && 'datagrid' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * @param ProductModelInterface $productModel
     * @param string                $locale
     *
     * @return string|null
     */
    private function getFamilyLabel(ProductModelInterface $productModel, string $locale) : ?string
    {
        $family = $productModel->getFamilyVariant()->getFamily();
        if (null === $family) {
            return null;
        }

        $translation = $family->getTranslation($locale);

        return $this->getLabel($family->getCode(), $translation->getLabel());
    }

    /**
     * @param string      $familyCode
     * @param string|null $familyLabel
     *
     * @return string
     */
    private function getLabel(string $familyCode, ?string $familyLabel) : string
    {
        return empty($familyLabel) ? sprintf('[%s]', $familyCode) : $familyLabel;
    }

    /**
     * @param ValueInterface $data
     * @param array          $context
     *
     * @return array|null
     */
    private function normalizeImage(?ValueInterface $data, array $context = []) : ?array
    {
        return $this->imageNormalizer->normalize($data, $context['data_locale']);
    }

    /**
     * Normalize the values of the productModel
     *
     * @param WriteValueCollection $values
     * @param string                   $format
     * @param array                    $context
     *
     * @return array
     */
    private function normalizeValues(WriteValueCollection $values, $format, array $context = []) : array
    {
        foreach ($context['filter_types'] as $filterType) {
            $values = $this->filter->filterCollection($values, $filterType, $context);
        }

        $data = $this->normalizer->normalize($values, $format, $context);

        return $data;
    }

    /**
     * @param ProductModelInterface $productModel
     *
     * @return null|string
     */
    private function getParentCode(ProductModelInterface $productModel): ?string
    {
        if (null !== $productModel->getParent()) {
            return $productModel->getParent()->getCode();
        }

        return null;
    }
}
