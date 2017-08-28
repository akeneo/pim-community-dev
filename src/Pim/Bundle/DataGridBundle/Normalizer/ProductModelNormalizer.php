<?php

declare(strict_types=1);

namespace Pim\Bundle\DataGridBundle\Normalizer;

use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
use Pim\Component\Catalog\Model\ValueInterface;
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
class ProductModelNormalizer implements NormalizerInterface, NormalizerAwareInterface
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
    public function normalize($productModel, $format = null, array $context = [])
    {
        if (!$this->normalizer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        $context = array_merge(['filter_types' => ['pim.transform.product_value.structured']], $context);
        $data = [];
        $locale = current($context['locales']);

        $data['identifier'] = $productModel->getCode();
        $data['family'] = $this->getFamilyLabel($productModel, $locale);
        $data['values'] = $this->normalizeValues($productModel->getValues(), $format, $context);
        $data['created'] = $this->normalizer->normalize($productModel->getCreated(), $format, $context);
        $data['updated'] = $this->normalizer->normalize($productModel->getUpdated(), $format, $context);
        $data['label'] = $productModel->getLabel($locale);
        $data['image'] = $this->normalizeImage($productModel->getImage(), $format, $context);

        $data['product_type'] = 'product_model';

        // TODO: PIM-6560 - Will show the number of complete products on the number of products (in the subtree)
        $data['variant_products'] = '';

        $data['groups'] = null;
        $data['enabled'] = null;
        $data['completeness'] = null;

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ProductModelInterface && 'datagrid' === $format;
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
     * @param string         $format
     * @param array          $context
     *
     * @return array|null
     */
    private function normalizeImage(?ValueInterface $data, $format, array $context = []) : ?array
    {
        return $this->normalizer->normalize($data, $format, $context)['data'];
    }

    /**
     * Normalize the values of the productModel
     *
     * @param ValueCollectionInterface $values
     * @param string                   $format
     * @param array                    $context
     *
     * @return array
     */
    private function normalizeValues(ValueCollectionInterface $values, $format, array $context = []) : array
    {
        foreach ($context['filter_types'] as $filterType) {
            $values = $this->filter->filterCollection($values, $filterType, $context);
        }

        $data = $this->normalizer->normalize($values, $format, $context);

        return $data;
    }
}
