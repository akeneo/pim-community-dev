<?php

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\StorageEvents;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Component\StorageUtils\Updater\PropertyCopierInterface;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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

    /** @var PropertyCopierInterface */
    protected $propertyCopier;

    /** @var ProductTemplateUpdaterInterface */
    protected $templateUpdater;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /**
     * @param PropertySetterInterface         $propertySetter
     * @param PropertyCopierInterface         $propertyCopier  will be removed in 1.6
     * @param ProductTemplateUpdaterInterface $templateUpdater
     */
    public function __construct(
        PropertySetterInterface $propertySetter,
        PropertyCopierInterface $propertyCopier,
        ProductTemplateUpdaterInterface $templateUpdater,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->propertySetter  = $propertySetter;
        $this->propertyCopier  = $propertyCopier;
        $this->templateUpdater = $templateUpdater;
        $this->eventDispatcher = $eventDispatcher;
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
                    'Expects a "Pim\Component\Catalog\Model\ProductInterface", "%s" provided.',
                    ClassUtils::getClass($product)
                )
            );
        }

        $options['data'] = $data;
        $this->eventDispatcher->dispatch(StorageEvents::PRE_UPDATE, new GenericEvent($product, $options));

        foreach ($data as $field => $values) {
            if (in_array($field, ['enabled', 'family', 'categories', 'variant_group', 'groups', 'associations'])) {
                $this->updateProductFields($product, $field, $values);
            } else {
                $this->updateProductValues($product, $field, $values);
            }
        }
        $this->updateProductVariantValues($product, $data);

        $this->eventDispatcher->dispatch(StorageEvents::POST_UPDATE, new GenericEvent($product, $options));

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
     * Sets the product values,
     *  - always set values related to family's attributes
     *  - sets optional values (not related to family's attributes) when a data is provided
     *  - sets optional values (not related to family's attributes) with empty data if value already exists
     *
     * @param ProductInterface $product
     * @param string           $attributeCode
     * @param array            $values
     */
    protected function updateProductValues(ProductInterface $product, $attributeCode, array $values)
    {
        $family = $product->getFamily();
        $authorizedCodes = (null !== $family) ? $family->getAttributeCodes() : [];
        $isFamilyAttribute = in_array($attributeCode, $authorizedCodes);

        foreach ($values as $value) {
            $hasValue = $product->getValue($attributeCode, $value['locale'], $value['scope']);
            $providedData = ('' === $value['data'] || [] === $value['data'] || null === $value['data']) ? false : true;

            if ($isFamilyAttribute || $providedData || $hasValue) {
                $options = ['locale' => $value['locale'], 'scope' => $value['scope']];
                $this->propertySetter->setData($product, $attributeCode, $value['data'], $options);
            }
        }
    }

    /**
     * Updates product with its variant group values to ensure that values coming from variant group are always
     * applied after the product values (if a product value is updated and should come from variant group)
     *
     * @param ProductInterface $product
     * @param array            $data
     */
    protected function updateProductVariantValues(ProductInterface $product, array $data)
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
}
