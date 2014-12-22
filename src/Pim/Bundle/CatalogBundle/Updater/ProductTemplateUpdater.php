<?php

namespace Pim\Bundle\CatalogBundle\Updater;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValue;
use Pim\Bundle\CatalogBundle\Util\ProductValueKeyGenerator;
use Pim\Bundle\TransformBundle\Builder\FieldNameBuilder;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Update many products at a time from the product template values
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductTemplateUpdater implements ProductTemplateUpdaterInterface
{
    /** @var ProductUpdaterInterface */
    protected $productUpdater;

    /** @var NormalizerInterface */
    protected $productValueNormalizer;

    /** @var FieldNameBuilder */
    protected $fieldNameBuilder;

    /**
     * @param ProductUpdaterInterface $productUpdater
     * @param NormalizerInterface     $productValueNormalizer
     * @param FieldNameBuilder        $fieldNameBuilder
     */
    public function __construct(
        ProductUpdaterInterface $productUpdater,
        NormalizerInterface $productValueNormalizer,
        FieldNameBuilder $fieldNameBuilder
    ) {
        $this->productUpdater = $productUpdater;
        $this->productValueNormalizer = $productValueNormalizer;
        $this->fieldNameBuilder = $fieldNameBuilder;
    }

    /**
     * {inheritdoc}
     */
    public function update(ProductTemplateInterface $template, array $products)
    {
        $rawValuesData = $template->getValuesData();
        $values = $this->denormalizeFromDB($rawValuesData);

        // Apply the update on another products
        $updates = $this->normalizeToUpdate($values);

        // TODO unset identifier and axis updates and picture (not supported for now)
        // TODO should be filtered before to save
        $skippedAttributes = ['sku', 'main_color', 'secondary_color', 'clothing_size', 'picture'];
        foreach ($updates as $indexUpdate => $update) {
            if (in_array($update['attribute'], $skippedAttributes)) {
                unset($updates[$indexUpdate]);
            } elseif (null === $update['value']) {
                // TODO ugly fix on null string
                $updates[$indexUpdate]['value'] = "";
            }
        }

        // TODO picture doesnt work
        // TODO prices doesnt work

        foreach ($updates as $update) {
            $this->productUpdater->setValue(
                $products,
                $update['attribute'],
                $update['value'],
                $update['locale'],
                $update['scope']
            );
        }
    }

    /**
     * @param array $rawProductValues
     *
     * @return ProductValueInterface[]
     *
     * TODO : will be re-worked with json format
     */
    protected function denormalizeFromDB(array $rawProductValues)
    {
        // TODO : inject class
        $productValueClass = 'Pim\Bundle\CatalogBundle\Model\ProductValue';
        $productValues = [];

        foreach ($rawProductValues as $attFieldName => $dataValue) {
            $attributeInfos = $this->fieldNameBuilder->extractAttributeFieldNameInfos($attFieldName);
            $attribute = $attributeInfos['attribute'];
            $value = new $productValueClass();
            $value->setAttribute($attribute);
            $value->setLocale($attributeInfos['locale_code']);
            $value->setScope($attributeInfos['scope_code']);
            unset($attributeInfos['attribute']);
            unset($attributeInfos['locale_code']);
            unset($attributeInfos['scope_code']);

            $productValues[] = $this->productValueNormalizer->denormalize(
                $dataValue,
                $productValueClass,
                'csv', // TODO json is coming
                ['entity' => $value] + $attributeInfos
            );
        }

        $valuesCollection = new ArrayCollection();
        foreach ($productValues as $value) {
            $valuesCollection[ProductValueKeyGenerator::getKey($value)] = $value;
        }

        return $valuesCollection;
    }

    /**
     * @param ArrayCollection $productValues
     *
     * @return array
     *
     * TODO : will be re-worked with json format, could become useless (same format to store and apply updates)
     */
    protected function normalizeToUpdate(ArrayCollection $productValues)
    {
        $normalizedValues = [];
        foreach ($productValues as $value) {
            $update = [
                'value' => $this->productValueNormalizer->normalize($value->getData(), 'json', ['locales' => []]),
                'attribute' => $value->getAttribute()->getCode(),
                'locale' => $value->getLocale(),
                'scope' => $value->getScope()
            ];
            $normalizedValues[] = $update;
        }

        return $normalizedValues;
    }
}
