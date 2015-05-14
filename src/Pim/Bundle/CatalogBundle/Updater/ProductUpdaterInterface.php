<?php

namespace Pim\Bundle\CatalogBundle\Updater;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Updates and validates a product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductUpdaterInterface extends UpdaterInterface
{
    /**
     * Updates a product with associative array of field to data (erase the current data)
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
     *
     * @param object $product The product to update
     * @param array  $data    The data to set
     * @param array  $options Options to pass to the setter
     *
     * @return ProductUpdaterInterface
     */
    public function update($product, array $data, array $options = []);

    /**
     * Sets the data in values of many products
     *
     * @param ProductInterface[] $products
     * @param string             $field
     * @param mixed              $data
     * @param string             $locale
     * @param string             $scope
     *
     * @return ProductUpdaterInterface
     *
     * @deprecated will be removed in 1.5, please use ProductFieldUpdaterInterface::setData(
     */
    public function setValue(array $products, $field, $data, $locale = null, $scope = null);

    /**
     * Copies a value from a source field to a destination field in many products
     *
     * @param ProductInterface[] $products
     * @param string             $fromField
     * @param string             $toField
     * @param string             $fromLocale
     * @param string             $toLocale
     * @param string             $fromScope
     * @param string             $toScope
     *
     * @return ProductUpdaterInterface
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
    );
}
