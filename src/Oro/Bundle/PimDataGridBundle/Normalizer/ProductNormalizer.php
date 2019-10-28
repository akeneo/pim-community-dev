<?php

namespace Oro\Bundle\PimDataGridBundle\Normalizer;

use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ImageNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Product normalizer for datagrid
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductNormalizer implements NormalizerInterface, NormalizerAwareInterface, CacheableSupportsMethodInterface
{
    use NormalizerAwareTrait;

    /** @var CollectionFilterInterface */
    private $filter;

    /** @var ImageNormalizer */
    protected $imageNormalizer;

    /** @var GetProductCompletenesses */
    private $getProductCompletenesses;

    public function __construct(
        CollectionFilterInterface $filter,
        ImageNormalizer $imageNormalizer,
        GetProductCompletenesses $getProductCompletenesses
    ) {
        $this->filter = $filter;
        $this->imageNormalizer = $imageNormalizer;
        $this->getProductCompletenesses = $getProductCompletenesses;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = [])
    {
        if (!$this->normalizer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        $context = array_merge(['filter_types' => ['pim.transform.product_value.structured']], $context);
        $data = [];
        $locale = current($context['locales']);
        $scope = current($context['channels']);

        $data['identifier'] = $product->getIdentifier();
        $data['family'] = $this->getFamilyLabel($product, $locale);
        $data['groups'] = $this->getGroupsLabels($product, $locale);
        $data['enabled'] = (bool) $product->isEnabled();
        $data['values'] = $this->normalizeValues($product->getValues(), $format, $context);
        $data['created'] = $this->normalizer->normalize($product->getCreated(), $format, $context);
        $data['updated'] = $this->normalizer->normalize($product->getUpdated(), $format, $context);
        $data['label'] = $product->getLabel($locale, $scope);
        $data['image'] = $this->normalizeImage($product->getImage(), $context);
        $data['completeness'] = $this->getCompletenessRatio($product, $context);
        $data['document_type'] = IdEncoder::PRODUCT_TYPE;
        $data['technical_id'] = $product->getId();
        $data['search_id'] = IdEncoder::encode($data['document_type'], $data['technical_id']);
        $data['is_checked'] = false;
        $data['complete_variant_product'] = null;
        $data['parent'] = $this->getParentCode($product);

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface && 'datagrid' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * @param ProductInterface $product
     * @param string           $locale
     *
     * @return string
     */
    protected function getFamilyLabel(ProductInterface $product, $locale)
    {
        $family = $product->getFamily();
        if (null === $family) {
            return null;
        }

        $translation = $family->getTranslation($locale);

        return $this->getLabel($family->getCode(), $translation->getLabel());
    }

    /**
     * @param ProductInterface $product
     * @param string           $locale
     *
     * @return string
     */
    protected function getGroupsLabels(ProductInterface $product, $locale)
    {
        $groups = [];
        foreach ($product->getGroups() as $group) {
            $translation = $group->getTranslation($locale);
            $groups[] = $this->getLabel($group->getCode(), $translation->getLabel());
        }

        return implode(', ', $groups);
    }

    /**
     * Get the completenesses of the product
     *
     * @param ProductInterface $product
     * @param array            $context
     *
     * @return int|null
     */
    protected function getCompletenessRatio(ProductInterface $product, array $context): ?int
    {
        $completenesses = $this->getProductCompletenesses->fromProductId($product->getId());
        $channel = current($context['channels']);
        $locale = current($context['locales']);
        $completeness = $completenesses->getCompletenessForChannelAndLocale($channel, $locale);

        return $completeness ? $completeness->ratio() : null;
    }

    /**
     * @param string      $code
     * @param string|null $value
     *
     * @return string
     */
    protected function getLabel($code, $value = null)
    {
        return '' === $value || null === $value ? sprintf('[%s]', $code) : $value;
    }

    /**
     * @param ValueInterface $data
     * @param array          $context
     *
     * @return array|null
     */
    protected function normalizeImage(?ValueInterface $data, array $context = [])
    {
        return $this->imageNormalizer->normalize($data, $context['data_locale']);
    }

    /**
     * Normalize the values of the product
     *
     * @param WriteValueCollection $values
     * @param string                   $format
     * @param array                    $context
     *
     * @return array
     */
    private function normalizeValues(WriteValueCollection $values, $format, array $context = [])
    {
        foreach ($context['filter_types'] as $filterType) {
            $values = $this->filter->filterCollection($values, $filterType, $context);
        }

        $data = $this->normalizer->normalize($values, $format, $context);

        return $data;
    }

    /**
     * @param EntityWithFamilyInterface $product
     *
     * @return null|string
     */
    private function getParentCode(EntityWithFamilyInterface $product): ?string
    {
        if ($product instanceof ProductInterface && $product->isVariant() && null !== $product->getParent()) {
            return $product->getParent()->getCode();
        }

        return null;
    }
}
