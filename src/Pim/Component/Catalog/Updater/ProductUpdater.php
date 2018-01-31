<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
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

    /** @var ObjectUpdaterInterface */
    protected $valuesUpdater;

    /** @var array */
    protected $supportedFields = [];

    /** @var array */
    protected $ignoredFields = [];

    /**
     * @param PropertySetterInterface         $propertySetter
     * @param ObjectUpdaterInterface          $valuesUpdater
     * @param array                           $supportedFields
     * @param array                           $ignoredFields
     */
    public function __construct(
        PropertySetterInterface $propertySetter,
        ObjectUpdaterInterface $valuesUpdater,
        array $supportedFields,
        array $ignoredFields
    ) {
        $this->propertySetter = $propertySetter;
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
    public function update($product, array $data, array $options = []): ProductUpdater
    {
        if (!$product instanceof ProductInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($product),
                ProductInterface::class
            );
        }

        foreach ($data as $code => $value) {
            $this->setData($product, $code, $value, $options);
        }

        return $this;
    }

    /**
     * @param ProductInterface $product
     * @param                  $field
     * @param                  $data
     * @param array            $options
     */
    protected function setData(ProductInterface $product, $field, $data, array $options = []): void
    {
        switch ($field) {
            case 'enabled':
            case 'family':
            case 'parent':
                $this->validateScalar($field, $data);
                $this->updateProductFields($product, $field, $data);
                break;
            case 'categories':
            case 'groups':
                $this->validateScalarArray($field, $data);
                $this->updateProductFields($product, $field, $data);
                break;
            case 'associations':
                $this->validateAssociationsDataType($data);
                $this->updateProductFields($product, $field, $data);
                break;
            case 'values':
                $this->valuesUpdater->update($product, $data, $options);
                $this->addEmptyValues($product, $data);
                break;
            default:
                if (!in_array($field, $this->ignoredFields)) {
                    throw UnknownPropertyException::unknownProperty($field);
                }
        }
    }

    /**
     * Validate association data
     *
     * @param $data
     *
     * @throws InvalidPropertyTypeException
     */
    protected function validateAssociationsDataType($data): void
    {
        if (!is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected(
                'associations',
                static::class,
                $data
            );
        }

        foreach ($data as $associationTypeCode => $associationTypeValues) {
            $this->validateScalar('associations', $associationTypeCode);
            if (!is_array($associationTypeValues)) {
                throw InvalidPropertyTypeException::arrayExpected(
                    'associations',
                    static::class,
                    $associationTypeValues
                );
            }

            foreach ($associationTypeValues as $property => $value) {
                $this->validateScalar('associations', $property);
                $this->validateScalarArray('associations', $value);
            }
        }
    }

    /**
     * Validate that it is a scalar value.
     *
     * @param $field
     * @param $data
     *
     * @throws InvalidPropertyTypeException
     */
    protected function validateScalar($field, $data): void
    {
        if (null !== $data && !is_scalar($data)) {
            throw InvalidPropertyTypeException::scalarExpected($field, static::class, $data);
        }
    }

    /**
     * Validate that it is an array with scalar values.
     *
     * @param string $field
     * @param mixed  $data
     *
     * @throws InvalidPropertyTypeException
     */
    protected function validateScalarArray($field, $data): void
    {
        if (!is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected($field, static::class, $data);
        }

        foreach ($data as $value) {
            if (null !== $value && !is_scalar($value)) {
                throw InvalidPropertyTypeException::validArrayStructureExpected(
                    $field,
                    sprintf('one of the %s is not a scalar', $field),
                    static::class,
                    $data
                );
            }
        }
    }

    /**
     * Sets the field
     *
     * @param ProductInterface $product
     * @param string           $field
     * @param mixed            $value
     */
    protected function updateProductFields(ProductInterface $product, $field, $value): void
    {
        $this->propertySetter->setData($product, $field, $value);
    }

    /**
     * Add empty values coming from the family of the $product
     *
     * TODO: TEMPORARY FIX, AS API-108 WILL HANDLE EMPTY VALUES
     *
     * @param ProductInterface $product
     * @param array            $values
     */
    private function addEmptyValues(ProductInterface $product, array $values): void
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
