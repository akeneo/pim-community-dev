<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Updater;

use Akeneo\Pim\Enrichment\Component\Product\Association\ParentAssociationsFilter;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnknownAttributeException;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\QuantifiedAssociation\QuantifiedAssociationsFromAncestorsFilter;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Validator\QuantifiedAssociationsStructureValidatorInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertySetterInterface;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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
    protected $ignoredFields = [];

    /** @var ParentAssociationsFilter */
    private $parentAssociationsFilter;

    /** @var QuantifiedAssociationsFromAncestorsFilter */
    private $quantifiedAssociationsFromAncestorsFilter;

    /** @var QuantifiedAssociationsStructureValidatorInterface */
    private $quantifiedAssociationsStructureValidator;

    /**
     * @param PropertySetterInterface $propertySetter
     * @param ObjectUpdaterInterface $valuesUpdater
     * @param ParentAssociationsFilter $parentAssociationsFilter
     * @param QuantifiedAssociationsFromAncestorsFilter $quantifiedAssociationsFromAncestorsFilter
     * @param QuantifiedAssociationsStructureValidatorInterface $quantifiedAssociationsStructureValidator
     * @param array $ignoredFields
     */
    public function __construct(
        PropertySetterInterface $propertySetter,
        ObjectUpdaterInterface $valuesUpdater,
        ParentAssociationsFilter $parentAssociationsFilter,
        QuantifiedAssociationsFromAncestorsFilter $quantifiedAssociationsFromAncestorsFilter,
        QuantifiedAssociationsStructureValidatorInterface $quantifiedAssociationsStructureValidator,
        array $ignoredFields
    ) {
        $this->propertySetter = $propertySetter;
        $this->valuesUpdater = $valuesUpdater;
        $this->ignoredFields = $ignoredFields;
        $this->parentAssociationsFilter = $parentAssociationsFilter;
        $this->quantifiedAssociationsFromAncestorsFilter = $quantifiedAssociationsFromAncestorsFilter;
        $this->quantifiedAssociationsStructureValidator = $quantifiedAssociationsStructureValidator;
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
     *      },
     *      "parent_associations": {
     *          "XSELL": {
     *              "groups": ["foo_group", "bar_group"],
     *              "product": ["foo_product", "bar_product"]
     *          }
     *      },
     *      "quantified_associations": {
     *          "PACK": {
     *              "products": [
     *                  {"identifier": "foo_product", "quantity": 3},
     *                  {"identifier": "bar_product", "quantity": 4},
     *              ],
     *              "product_models": [
     *                  {"identifier": "foo_product_model", "quantity": 3},
     *                  {"identifier": "bar_product_model", "quantity": 4},
     *              ],
     *          },
     *      },
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

        $data = $this->reorderParentProperty($data);

        foreach ($data as $code => $value) {
            if (!is_string($code)) {
                throw new BadRequestHttpException('Invalid json message received');
            }
            $filteredValue = $this->filterData($product, $code, $value, $data);
            $this->setData($product, $code, $filteredValue, $options);
        }

        return $this;
    }

    protected function filterData(ProductInterface $product, string $field, $data, array $context = [])
    {
        switch ($field) {
            case 'associations':
                $this->validateAssociationsDataType($data);
                if (isset($context['parent_associations'])) {
                    $data = $this->filterParentAssociations($data, $context['parent_associations']);
                }
                break;
            case 'quantified_associations':
                $this->quantifiedAssociationsStructureValidator->validate('quantified_associations', $data);
                $data = $this->filterQuantifiedAssociationsFromAncestors($product, $data);
                break;
        }

        return $data;
    }

    /**
     * @throws UnknownAttributeException
     */
    protected function setData(ProductInterface $product, string $field, $data, array $options = []): void
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
            case 'quantified_associations':
                $this->updateProductFields($product, $field, $data);
                break;
            case 'values':
                try {
                    $this->valuesUpdater->update($product, $data, $options);
                } catch (UnknownPropertyException $exception) {
                    throw new UnknownAttributeException($exception->getPropertyName(), $exception);
                }
                break;
            default:
                if (!in_array($field, $this->ignoredFields)) {
                    throw UnknownPropertyException::unknownProperty($field);
                }
        }
    }

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

    protected function filterParentAssociations(array $associations, ?array $parentAssociations): array
    {
        if (null === $parentAssociations) {
            return $associations;
        }

        $associations = $this->parentAssociationsFilter->filterParentAssociations(
            $associations,
            $parentAssociations
        );

        return $associations;
    }

    protected function filterQuantifiedAssociationsFromAncestors(
        ProductInterface $product,
        array $data
    ): array {
        return $this->quantifiedAssociationsFromAncestorsFilter->filter(
            $data,
            $product
        );
    }

    protected function validateScalar(string $field, $data): void
    {
        if (null !== $data && !is_scalar($data)) {
            throw InvalidPropertyTypeException::scalarExpected($field, static::class, $data);
        }
    }

    protected function validateScalarArray(string $field, $data): void
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

    protected function updateProductFields(ProductInterface $product, string $field, $value): void
    {
        $this->propertySetter->setData($product, $field, $value);
    }

    /**
     * This method order the data by setting the parent field first. It comes from the ParentFieldSetter that sets the
     * family from the parent if the product family is null. By doing this the validator does not fail if the family
     * field has been set to null from the API. So to prevent this we order the parent before the family field. this way
     * the field family will be updated to null if the data sent from the API for the family field is null.
     *
     * Example:
     *
     * {
     *     "identifier": "test",
     *     "family": null,
     *     "parent": "amor"
     * }
     *
     * This example does not work because the parent setter will set the family with the parent family.
     *
     * @param array $data
     * @return array
     */
    private function reorderParentProperty(array $data): array
    {
        if (!isset($data['parent'])) {
            return $data;
        }

        return ['parent' => $data['parent']] + $data;
    }
}
