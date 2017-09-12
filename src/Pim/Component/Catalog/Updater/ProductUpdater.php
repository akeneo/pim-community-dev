<?php

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Updates a product
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductUpdater implements ObjectUpdaterInterface
{
    /** @var PropertySetterInterface */
    protected $propertySetter;

    /** @var ProductTemplateUpdaterInterface */
    protected $templateUpdater;

    /** @var ObjectUpdaterInterface */
    protected $valuesUpdater;

    /** @var array */
    protected $supportedFields = [];

    /** @var array */
    protected $ignoredFields = [];

    /**
     * @param PropertySetterInterface         $propertySetter
     * @param ProductTemplateUpdaterInterface $templateUpdater
     * @param ObjectUpdaterInterface          $valuesUpdater
     * @param array                           $supportedFields
     * @param array                           $ignoredFields
     */
    public function __construct(
        PropertySetterInterface $propertySetter,
        ProductTemplateUpdaterInterface $templateUpdater,
        ObjectUpdaterInterface $valuesUpdater,
        array $supportedFields,
        array $ignoredFields
    ) {
        $this->propertySetter = $propertySetter;
        $this->templateUpdater = $templateUpdater;
        $this->valuesUpdater = $valuesUpdater;
        $this->supportedFields = $supportedFields;
        $this->ignoredFields = $ignoredFields;
    }

    /**
     * {@inheritdoc}
     *
     * {
     *      "identifier": "my-sku",
     *      "values": {
     *          "sku": [{
     *             "locale": null,
     *             "scope":  null,
     *             "data":  "my-sku",
     *          }],
     *          "name": [{
     *              "locale": "fr_FR",
     *              "scope":  null,
     *              "data":  "T-shirt super beau",
     *          }],
     *          "description": [
     *               {
     *                   "locale": "en_US",
     *                   "scope":  "mobile",
     *                   "data":   "My description"
     *               },
     *               {
     *                   "locale": "fr_FR",
     *                   "scope":  "mobile",
     *                   "data":   "Ma description mobile"
     *               },
     *               {
     *                   "locale": "en_US",
     *                   "scope":  "ecommerce",
     *                   "data":   "My description for the website"
     *               },
     *          ],
     *          "price": [
     *               {
     *                   "locale": null,
     *                   "scope":  ecommerce,
     *                   "data":   [
     *                       {"amount": 10, "currency": "EUR"},
     *                       {"amount": 24, "currency": "USD"},
     *                       {"amount: 20, "currency": "CHF"}
     *                   ]
     *               }
     *               {
     *                   "locale": null,
     *                   "scope":  mobile,
     *                   "data":   [
     *                       {"amount": 11, "currency": "EUR"},
     *                       {"amount": 25, "currency": "USD"},
     *                       {"amount": 21, "currency": "CHF"}
     *                   ]
     *               }
     *          ],
     *          "length": [{
     *              "locale": "en_US",
     *              "scope":  "mobile",
     *              "data":   {"amount": "10", "unit": "CENTIMETER"}
     *          }]
     *      },
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
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($product),
                ProductInterface::class
            );
        }

        foreach ($data as $code => $values) {
            if (in_array($code, $this->supportedFields)) {
                $this->updateProductFields($product, $code, $values);
            } elseif ('values' === $code) {
                $this->valuesUpdater->update($product, $values, $options);
                $this->addEmptyValues($product, $values);
            } elseif (!in_array($code, $this->ignoredFields)) {
                throw UnknownPropertyException::unknownProperty($code);
            }
        }
        $this->updateVariantProductValues($product, $data);

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
        $this->propertySetter->setData($product, $field, $value);
    }

    /**
     * Updates product with its variant group values to ensure that values coming from variant group are always
     * applied after the product values (if a product value is updated and should come from variant group)
     *
     * @param ProductInterface $product
     * @param array            $data
     */
    protected function updateVariantProductValues(ProductInterface $product, array $data)
    {
        $variantGroup = $product->getVariantGroup();
        $shouldEraseData = false;
        if (null !== $variantGroup && null !== $variantGroup->getProductTemplate()) {
            $template = $variantGroup->getProductTemplate();
            foreach (array_keys($data) as $field) {
                if ($template->hasValueForAttributeCode($field) || null === $product->getValue($field)) {
                    $shouldEraseData = true;
                }
            }
            if ($shouldEraseData) {
                $this->templateUpdater->update($template, [$product]);
            }
        }
    }

    /**
     * Add empty values coming from the family of the $product
     *
     * TODO: TEMPORARY FIX, AS API-108 WILL HANDLE EMPTY VALUES
     *
     * @param ProductInterface $product
     * @param array            $values
     */
    private function addEmptyValues(ProductInterface $product, array $values)
    {
        $family = $product->getFamily();
        $authorizedCodes = (null !== $family) ? $family->getAttributeCodes() : [];

        foreach ($values as $code => $value) {
            $isFamilyAttribute = in_array($code, $authorizedCodes);

            foreach ($value as $data) {
                $emptyData = ('' === $data['data'] || [] === $data['data'] || null === $data['data']);

                if ($isFamilyAttribute && $emptyData) {
                    $options = ['locale' => $data['locale'], 'scope' => $data['scope']];
                    $this->propertySetter->setData($product, $code, $data['data'], $options);
                }
            }
        }
    }
}
