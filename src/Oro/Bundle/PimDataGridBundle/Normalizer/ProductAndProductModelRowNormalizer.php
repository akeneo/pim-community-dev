<?php

namespace Oro\Bundle\PimDataGridBundle\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Grid\ReadModel\Row;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ImageNormalizer;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Webmozart\Assert\Assert;

/**
 * Normalize product and product model row.
 * It must be agnostic of the row: the behavior should be the same between a product row
 * and a product model row.
 *
 * These differences of behavior should be encapsulate by the row object itself.
 *
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAndProductModelRowNormalizer implements NormalizerInterface, NormalizerAwareInterface, CacheableSupportsMethodInterface
{
    use NormalizerAwareTrait;

    /** @var ImageNormalizer */
    protected $imageNormalizer;

    /**
     * @param ImageNormalizer $imageNormalizer
     */
    public function __construct(ImageNormalizer $imageNormalizer)
    {
        $this->imageNormalizer = $imageNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($row, $format = null, array $context = [])
    {
        Assert::isInstanceOf($this->normalizer, NormalizerInterface::class);
        Assert::isInstanceOf($row, Row::class);
        Assert::eq($format, 'datagrid');

        $data['identifier'] = $row->identifier();
        $data['family'] = $row->familyCode();
        $data['groups'] = implode(',', $row->groupCodes());
        $data['enabled'] = $row->enabled();
        $data['values'] = $this->normalizer->normalize($row->values(), 'datagrid', $context);
        $data['created'] = $this->normalizer->normalize($row->created(), $format, $context);
        $data['updated'] = $this->normalizer->normalize($row->updated(), $format, $context);
        $data['label'] = $row->label();
        $data['image'] = $this->imageNormalizer->normalize($row->image(), $context['data_locale']);
        $data['completeness'] = $row->completeness();
        $data['document_type'] = $row->documentType();
        $data['technical_id'] = $row->technicalId();
        $data['id'] = $row->technicalId();
        $data['search_id'] = $row->searchId();
        $data['is_checked'] = $row->checked();
        $data['complete_variant_product'] = $row->childrenCompleteness();
        $data['parent'] = $row->parentCode();

        foreach ($row->additionalProperties() as $additionalProperty) {
            $data[$additionalProperty->name()] = $additionalProperty->value();
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Row && 'datagrid' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
