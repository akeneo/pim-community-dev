<?php

namespace Oro\Bundle\PimDataGridBundle\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ImageNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

/**
 * Product association normalizer for datagrid
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAssociationNormalizer implements NormalizerInterface, SerializerAwareInterface, CacheableSupportsMethodInterface
{
    use SerializerAwareTrait;

    /** @var ImageNormalizer */
    protected $imageNormalizer;

    /** @var GetProductCompletenesses */
    private $getProductCompletenesses;

    public function __construct(
        ImageNormalizer $imageNormalizer,
        GetProductCompletenesses $getProductCompletenesses
    ) {
        $this->imageNormalizer = $imageNormalizer;
        $this->getProductCompletenesses = $getProductCompletenesses;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = [])
    {
        if (!$this->serializer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        $data = [];
        $locale = current($context['locales']);
        $channel = current($context['channels']);

        $data['identifier'] = $product->getIdentifier();
        $data['family'] = $this->getFamilyLabel($product, $locale);
        $data['enabled'] = (bool) $product->isEnabled();
        $data['created'] = $this->serializer->normalize($product->getCreated(), $format, $context);
        $data['updated'] = $this->serializer->normalize($product->getUpdated(), $format, $context);

        $data['is_checked'] = $context['is_associated'];
        $data['is_associated'] = $context['is_associated'];
        $data['label'] = $product->getLabel($locale, $channel);
        $data['completeness'] = $this->getCompletenessRatio($product, $context);
        $data['image'] = $this->imageNormalizer->normalize($product->getImage(), $context['data_locale']);

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
}
