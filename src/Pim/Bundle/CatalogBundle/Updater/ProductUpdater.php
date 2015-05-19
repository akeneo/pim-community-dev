<?php

namespace Pim\Bundle\CatalogBundle\Updater;

use Akeneo\Component\StorageUtils\Updater\UpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Updates a product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductUpdater implements UpdaterInterface, ProductUpdaterInterface
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
     *
     * {
     *      "name": [{
     *          "locale": "fr_FR",
     *          "scope":  null,
     *          "data":  "T-shirt super beau",
     *      }],
     *      "description": [
     *           {
     *               "locale": "en_US",
     *               "scope":  "mobile",
     *               "data":   "My description"
     *           },
     *           {
     *               "locale": "fr_FR",
     *               "scope":  "mobile",
     *               "data":   "Ma description mobile"
     *           },
     *           {
     *               "locale": "en_US",
     *               "scope":  "ecommerce",
     *               "data":   "My description for the website"
     *           },
     *      ],
     *      "price": [
     *           {
     *               "locale": null,
     *               "scope":  ecommerce,
     *               "data":   [
     *                   {"data": 10, "currency": "EUR"},
     *                   {"data": 24, "currency": "USD"},
     *                   {"data": 20, "currency": "CHF"}
     *               ]
     *           }
     *           {
     *               "locale": null,
     *               "scope":  mobile,
     *               "data":   [
     *                   {"data": 11, "currency": "EUR"},
     *                   {"data": 25, "currency": "USD"},
     *                   {"data": 21, "currency": "CHF"}
     *               ]
     *           }
     *      ],
     *      "length": [{
     *          "locale": "en_US",
     *          "scope":  "mobile",
     *          "data":   {"data": "10", "unit": "CENTIMETER"}
     *      }],
     *      "enabled": true,
     *      "categories": ["tshirt", "men"],
     *      "associations": {
     *          "XSELL": {
     *              "groups": ["akeneo_tshirt", "oro_tshirt"],
     *              "product": ["AKN_TS", "ORO_TSH"]
     *          }
     *      }
     * }
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
            if (in_array($field, ['enabled', 'family', 'categories', 'groups', 'associations'])) {
                $this->updateProductFields($product, $field, $values);
            } else {
                $this->updateProductValues($product, $field, $values);
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated will be removed in 1.5, please use ProductFieldUpdaterInterface::setData(
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
     *
     * @deprecated will be removed in 1.5, please use ProductFieldUpdaterInterface::copyData(
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

    /**
     * Sets the field
     *
     * @param ProductInterface $product
     * @param string           $field
     * @param mixed            $value
     */
    protected function updateProductFields(ProductInterface $product, $field, $value)
    {
        $this->productFieldUpdater->setData($product, $field, $value);
    }

    /**
     * Sets the value if the attribute belongs to the family or if the value already exists as optional
     *
     * @param ProductInterface $product
     * @param string           $field
     * @param array            $values
     */
    protected function updateProductValues(ProductInterface $product, $field, array $values)
    {
        foreach ($values as $value) {
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
