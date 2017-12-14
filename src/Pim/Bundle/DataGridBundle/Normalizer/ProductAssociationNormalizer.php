<?php

namespace Pim\Bundle\DataGridBundle\Normalizer;

use Pim\Bundle\EnrichBundle\Normalizer\ImageNormalizer;
use Pim\Component\Catalog\Model\ProductInterface;
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
class ProductAssociationNormalizer implements NormalizerInterface, SerializerAwareInterface
{
    use SerializerAwareTrait;

    /** @var ImageNormalizer */
    protected $imageNormalizer;

    /**
     * @param ImageNormalizer           $imageNormalizer
     */
    public function __construct(ImageNormalizer $imageNormalizer)
    {
        $this->imageNormalizer = $imageNormalizer;
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

        $data['identifier'] = $product->getIdentifier();
        $data['family'] = $this->getFamilyLabel($product, $locale);
        $data['enabled'] = (bool) $product->isEnabled();
        $data['created'] = $this->serializer->normalize($product->getCreated(), $format, $context);
        $data['updated'] = $this->serializer->normalize($product->getUpdated(), $format, $context);

        $data['is_checked'] = $context['is_associated'];
        $data['is_associated'] = $context['is_associated'];
        $data['label'] = $product->getLabel($locale);
        $data['completeness'] = $this->getCompleteness($product, $context);
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
}
