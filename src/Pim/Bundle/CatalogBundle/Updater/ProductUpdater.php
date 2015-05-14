<?php

namespace Pim\Bundle\CatalogBundle\Updater;

use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Provides basic operations to update a product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductUpdater implements ProductUpdaterInterface
{
    /** @var ProductFieldUpdaterInterface */
    protected $productFieldUpdater;

    /**
     * @param ProductFieldUpdaterInterface $productFieldUpdater
     */
    public function __construct(ProductFieldUpdaterInterface $productFieldUpdater)
    {
        $this->productFieldUpdater = $productFieldUpdater;
    }

    /**
     * {@inheritdoc}
     */
    public function update($product, array $data, array $options = [])
    {
        if (!$product instanceof ProductInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Pim\Bundle\CatalogBundle\Model\ProductInterface", "%s" provided.',
                    ClassUtils::getClass($product)
                )
            );
        }

        foreach ($data as $field => $values) {
            // TODO: hard coded :(
            if (in_array($field, ['enabled', 'categories', 'groups', 'associations'])) {
                $this->productFieldUpdater->setData($product, $field, $values, []);
            } else {
                foreach ($values as $value) {
                    // sets the value if the attribute belongs to the family or if the value already exists as optional
                    $family = $product->getFamily();
                    $belongsToFamily = $family === null ? false : $family->hasAttributeCode($field);
                    $hasValue = $product->getValue($field, $value['locale'], $value['scope']) !== null;
                    if ($belongsToFamily || $hasValue) {
                        $options = ['locale' => $value['locale'], 'scope' => $value['scope']];
                        $this->productFieldUpdater->setData($product, $field, $value['data'], $options);
                    }
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setValue(array $products, $field, $data, $locale = null, $scope = null)
    {
        foreach ($products as $product) {
            $this->productFieldUpdater->setData($product, $field, $data, ['locale' => $locale, 'scope' => $scope]);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function copyValue(
        array $products,
        $fromField,
        $toField,
        $fromLocale = null,
        $toLocale = null,
        $fromScope = null,
        $toScope = null
    ) {
        $options = [
            'from_locale' => $fromLocale,
            'to_locale' => $toLocale,
            'from_scope' => $fromScope,
            'to_scope' => $toScope,
        ];
        foreach ($products as $product) {
            $this->productFieldUpdater->copyData($product, $product, $fromField, $toField, $options);
        }

        return $this;
    }
}
