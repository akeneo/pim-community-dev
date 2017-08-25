<?php

namespace Pim\Bundle\DataGridBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Model\ValueInterface;
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
class ProductNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

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
     */
    public function normalize($product, $format = null, array $context = [])
    {
        if (!$this->normalizer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        $context = array_merge(['filter_types' => ['pim.transform.product_value.structured']], $context);
        $data = [];
        $locale = current($context['locales']);

        $data['identifier'] = $product->getIdentifier();
        $data['family'] = $this->getFamilyLabel($product, $locale);
        $data['groups'] = $this->getGroupsLabels($product, $locale);
        $data['enabled'] = (bool) $product->isEnabled();
        $data['values'] = $this->normalizeValues($product->getValues(), $format, $context);
        $data['created'] = $this->normalizer->normalize($product->getCreated(), $format, $context);
        $data['updated'] = $this->normalizer->normalize($product->getUpdated(), $format, $context);
        $data['label'] = $product->getLabel($locale);
        $data['image'] = $this->normalizeImage($product->getImage(), $format, $context);
        $data['completeness'] = $this->getCompleteness($product, $context);

        $data['product_type'] = 'product';
        $data['variant_products'] = null;

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductInterface && 'datagrid' === $format;
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
    protected function getCompleteness(ProductInterface $product, array $context)
    {
        $completenesses = null;
        $locale = current($context['locales']);
        $channel = current($context['channels']);

        foreach ($product->getCompletenesses() as $completeness) {
            if ($completeness->getChannel()->getCode() === $channel &&
                $completeness->getLocale()->getCode() === $locale) {
                $completenesses = $completeness->getRatio();
            }
        }

        return $completenesses;
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
     * @param string         $format
     * @param array          $context
     *
     * @return array|null
     */
    protected function normalizeImage(?ValueInterface $data, $format, $context = [])
    {
        return $this->normalizer->normalize($data, $format, $context)['data'];
    }

    /**
     * Normalize the values of the product
     *
     * @param ValueCollectionInterface $values
     * @param string                   $format
     * @param array                    $context
     *
     * @return array
     */
    private function normalizeValues(ValueCollectionInterface $values, $format, array $context = [])
    {
        foreach ($context['filter_types'] as $filterType) {
            $values = $this->filter->filterCollection($values, $filterType, $context);
        }

        $data = $this->normalizer->normalize($values, $format, $context);

        return $data;
    }
}
