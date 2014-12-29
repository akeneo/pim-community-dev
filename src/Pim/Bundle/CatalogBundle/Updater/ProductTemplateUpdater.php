<?php

namespace Pim\Bundle\CatalogBundle\Updater;

use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValue;
use Pim\Bundle\CatalogBundle\Util\ProductValueKeyGenerator;
use Pim\Bundle\TransformBundle\Builder\FieldNameBuilder;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
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

    /** @var DenormalizerInterface */
    protected $productValueDenormalizer;

    /**
     * @param ProductUpdaterInterface $productUpdater
     * @param NormalizerInterface     $productValueNormalizer
     * @param DenormalizerInterface   $productValueDenormalizer
     */
    public function __construct(
        ProductUpdaterInterface $productUpdater,
        NormalizerInterface $productValueNormalizer,
        DenormalizerInterface $productValueDenormalizer
    ) {
        $this->productUpdater = $productUpdater;
        $this->productValueNormalizer = $productValueNormalizer;
        $this->productValueDenormalizer = $productValueDenormalizer;
    }

    /**
     * {inheritdoc}
     */
    public function update(ProductTemplateInterface $template, array $products)
    {
        /**
         * TODO once we'll use json format to store values, we'll be able to directly update products
         * product updater uses json format too
         *
         * Replace all the following by `$updates = $template->getValuesData();`
         */
        $rawValuesData = $template->getValuesData();
        $values = $this->denormalizeFromDB($rawValuesData);
        $updates = $this->normalizeToUpdate($values);
        // TODO unset identifier and axis updates and picture (not supported for now)
        /** end of stuff to replace, denormalizeFromDB and normalizeToUpdate will be dropped too */

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
     * TODO : will be dropped once json format used
     */
    protected function denormalizeFromDB(array $rawProductValues)
    {
        return $this->productValueDenormalizer->denormalize($rawProductValues, 'variant_group_values', 'csv');
    }

    /**
     * @param ProductValueInterface[]
     *
     * @return array
     *
     * TODO : will be dropped once json format used
     */
    protected function normalizeToUpdate($productValues)
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
