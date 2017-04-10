<?php

namespace Pim\Bundle\DataGridBundle\Normalizer;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

/**
 * Product association normalizer for datagrid
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAssociationNormalizer extends SerializerAwareNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($product, $format = null, array $context = [])
    {
        if (!$this->serializer instanceof NormalizerInterface) {
            throw new \LogicException('Serializer must be a normalizer');
        }

        if (!isset($context['current_product']) || !$context['current_product'] instanceof ProductInterface) {
            throw InvalidObjectException::objectExpected($context['current_product'], ProductInterface::class);
        }

        $data = [];
        $locale = current($context['locales']);

        $data['identifier'] = $product->getIdentifier();
        $data['family'] = $this->getFamilyLabel($product, $locale);
        $data['enabled'] = (bool) $product->isEnabled();
        $data['created'] = $this->serializer->normalize($product->getCreated(), $format, $context);
        $data['updated'] = $this->serializer->normalize($product->getUpdated(), $format, $context);

        $isAssociated = $this->isAssociated($context['current_product'], $product, $context['association_type_id']);
        $data['is_checked'] = $isAssociated;
        $data['is_associated'] = $isAssociated;
        $data['label'] = $product->getLabel($locale);
        $data['completeness'] = $this->getCompleteness($product, $context);

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

    /**
     * @param ProductInterface $currentProduct
     * @param ProductInterface $product
     * @param int              $associationTypeId
     *
     * @return bool
     */
    protected function isAssociated(ProductInterface $currentProduct, ProductInterface $product, $associationTypeId)
    {
        foreach ($currentProduct->getAssociations() as $association) {
            if ($association->getAssociationType()->getId() == $associationTypeId) {
                foreach ($association->getProducts() as $associatedProduct) {
                    if ($associatedProduct->getId() === $product->getId()) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
