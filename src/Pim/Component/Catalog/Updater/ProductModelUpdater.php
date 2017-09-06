<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Exception\ImmutablePropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Comparator\ComparatorRegistryInterface;
use Pim\Component\Catalog\FamilyVariant\EntityWithFamilyVariantAttributesProvider;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelUpdater implements ObjectUpdaterInterface
{
    /** @var PropertySetterInterface */
    private $propertySetter;

    /** @var ObjectUpdaterInterface */
    private $valuesUpdater;

    /** @var array */
    private $supportedFields;

    /** @var array */
    private $ignoredFields;

    /** @var IdentifiableObjectRepositoryInterface */
    private $familyVariantRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    private $productModelRepository;

    /** @var NormalizerInterface */
    private $valueNormalizer;

    /** @var EntityWithFamilyVariantAttributesProvider */
    private $attributesProvider;

    /** @var ComparatorRegistryInterface */
    private $comparatorRegistry;

    /**
     * @param PropertySetterInterface                   $propertySetter
     * @param ObjectUpdaterInterface                    $valuesUpdater
     * @param IdentifiableObjectRepositoryInterface     $familyVariantRepository
     * @param IdentifiableObjectRepositoryInterface     $productModelRepository
     * @param NormalizerInterface                       $valueNormalizer
     * @param EntityWithFamilyVariantAttributesProvider $attributesProvider
     * @param ComparatorRegistryInterface               $comparatorRegistry
     * @param array                                     $supportedFields
     * @param array                                     $ignoredFields
     */
    public function __construct(
        PropertySetterInterface $propertySetter,
        ObjectUpdaterInterface $valuesUpdater,
        IdentifiableObjectRepositoryInterface $familyVariantRepository,
        IdentifiableObjectRepositoryInterface $productModelRepository,
        NormalizerInterface $valueNormalizer,
        EntityWithFamilyVariantAttributesProvider $attributesProvider,
        ComparatorRegistryInterface $comparatorRegistry,
        array $supportedFields,
        array $ignoredFields
    ) {
        $this->propertySetter = $propertySetter;
        $this->valuesUpdater = $valuesUpdater;
        $this->familyVariantRepository = $familyVariantRepository;
        $this->productModelRepository = $productModelRepository;
        $this->valueNormalizer = $valueNormalizer;
        $this->attributesProvider = $attributesProvider;
        $this->comparatorRegistry = $comparatorRegistry;
        $this->supportedFields = $supportedFields;
        $this->ignoredFields = $ignoredFields;
    }

    /**
     * {@inheritdoc}
     */
    public function update($productModel, array $data, array $options = [])
    {
        if (!$productModel instanceof ProductModelInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($productModel),
                ProductModelInterface::class
            );
        }

        if (isset($data['parent'])) {
            $this->updateParentAndFamily($productModel, $data);
            unset($data['parent']);
        }

        foreach ($data as $code => $value) {
            if ('values' === $code) {
                $this->updateValues($productModel, $value, $options);
            } elseif ('code' === $code) {
                $productModel->setCode($value);
            } elseif ('family_variant' === $code) {
                $this->updateFamilyVariant($productModel, $value);
            } elseif (in_array($code, $this->supportedFields)) {
                $this->propertySetter->setData($productModel, $code, $value);
            } elseif (!in_array($code, $this->ignoredFields)) {
                throw UnknownPropertyException::unknownProperty($code);
            }
        }

        return $this;
    }

    /**
     * As for a sub product model, the family variant can be guessed from the
     * parent, it needs to be set first, then the family.
     * However, the family variant will be automatically set only if there is not
     * already one and if the product model has a parent.
     *
     * @param ProductModelInterface $productModel
     * @param array                 $data
     */
    private function updateParentAndFamily(ProductModelInterface $productModel, array $data): void
    {
        $this->updateParent($productModel, $data['parent']);

        if (null === $productModel->getFamilyVariant() &&
            (!isset($data['family_variant']) || '' === $data['family_variant'])
        ) {
            $productModel->setFamilyVariant($productModel->getParent()->getFamilyVariant());
        }
    }

    /**
     * Updates the values of the product model.
     *
     * If the product model already exists, we ensure we do not update variant
     * axes values: provided values must be either missing, empty, or identical
     * to the existing ones, or an exception will be thrown.
     *
     * @param ProductModelInterface $productModel
     * @param array                 $values
     * @param array                 $options
     *
     * @throws ImmutablePropertyException
     */
    private function updateValues(ProductModelInterface $productModel, array $values, array $options): void
    {
        if (null !== $productModel->getId()) {
            $axesCodesAndTypes = $this->getProductModelAxesCodesAndTypes($productModel);
            $newAxesValues = $this->getNewVariantAxesValues($values, array_keys($axesCodesAndTypes));

            if (!empty($newAxesValues)) {
                $currentAxesValues = $this->getCurrentVariantAxesValues($productModel, array_keys($axesCodesAndTypes));
                $willBeUpdatedValues = $this->compareVariantAxesValues(
                    $currentAxesValues,
                    $newAxesValues,
                    $axesCodesAndTypes
                );

                if (!empty($willBeUpdatedValues)) {
                    throw ImmutablePropertyException::immutableProperty(
                        implode(', ', array_keys($willBeUpdatedValues)),
                        implode(', ', $willBeUpdatedValues),
                        static::class
                    );
                }
            }
        }

        $this->valuesUpdater->update($productModel, $values, $options);
    }

    /**
     * Returns the list of the variant axes codes, associated to their attribute type:
     *
     * [
     *     'attribute_code' => 'attribute_type',
     * ]
     *
     * @param ProductModelInterface $productModel
     *
     * @return array
     */
    private function getProductModelAxesCodesAndTypes(ProductModelInterface $productModel): array
    {
        $productModelAxesCodesAndTypes = [];

        foreach ($this->attributesProvider->getAxes($productModel) as $attribute) {
            $productModelAxesCodesAndTypes[$attribute->getCode()] = $attribute->getType();
        }

        return $productModelAxesCodesAndTypes;
    }

    /**
     * Removes all values except the ones of the variant axes.
     *
     * The provided values (and so the result) are in standard format.
     *
     * @param array $values
     * @param array $axesCodes
     *
     * @return array
     */
    private function getNewVariantAxesValues(array $values, array $axesCodes): array
    {
        $attributeCodes = array_keys($values);
        foreach ($attributeCodes as $attributeCode) {
            if (!in_array($attributeCode, $axesCodes)) {
                unset($values[$attributeCode]);
            }
        }

        return $values;
    }

    /**
     * Gets the current values of the product model variant axes.
     *
     * The returned result is in standard format.
     *
     * @param ProductModelInterface $productModel
     * @param array                 $axesCodes
     *
     * @return array
     */
    private function getCurrentVariantAxesValues(ProductModelInterface $productModel, array $axesCodes): array
    {
        $currentAxesValues = [];
        foreach ($axesCodes as $axisCode) {
            $currentAxesValues[$axisCode] = [
                $this->valueNormalizer->normalize($productModel->getValue($axisCode), 'standard'),
            ];
        };

        return $currentAxesValues;
    }

    /**
     * Compares the current values of the variant axes against the new ones we
     * want to update the product model with.
     *
     * Returns the new values if they are different, and an empty array if there
     * is no difference.
     *
     * @param array $currentAxesValues
     * @param array $newAxesValues
     * @param array $axesCodesAndTypes
     *
     * @return array
     */
    private function compareVariantAxesValues(
        array $currentAxesValues,
        array $newAxesValues,
        array $axesCodesAndTypes
    ): array {
        $updateValues = [];

        foreach (array_keys($axesCodesAndTypes) as $axisCode) {
            if (array_key_exists($axisCode, $currentAxesValues) && array_key_exists($axisCode, $newAxesValues)) {
                $comparator = $this->comparatorRegistry->getAttributeComparator($axesCodesAndTypes[$axisCode]);
                $diff = $comparator->compare($newAxesValues[$axisCode][0], $currentAxesValues[$axisCode][0]);
                if (null !== $diff) {
                    $updateValues[$axisCode] = is_array($diff['data']) ? implode(' ', $diff['data']) : $diff['data'];
                }
            }
        }

        return $updateValues;
    }

    /**
     * Updates the parent of the product model.
     * If the product model is a root one, it cannot have a parent, so an
     * exception will be thrown.
     *
     * @param ProductModelInterface $productModel
     * @param string                $parentCode
     *
     * @throws ImmutablePropertyException
     * @throws InvalidPropertyException
     */
    private function updateParent(ProductModelInterface $productModel, string $parentCode): void
    {
        if (empty($parentCode)) {
            return;
        }

        // TODO: To remove in PIM-6350.
        if (null !== $productModel->getId() && (
            $productModel->isRootProductModel() ||
            (null !== $productModel->getParent() && $parentCode !== $productModel->getParent()->getCode())
        )) {
            throw ImmutablePropertyException::immutableProperty(
                'parent',
                $parentCode,
                static::class
            );
        }

        if (null === $parentProductModel = $this->productModelRepository->findOneByIdentifier($parentCode)) {
            throw InvalidPropertyException::validEntityCodeExpected(
                'parent',
                'parent code',
                'The product model does not exist',
                static::class,
                $parentCode
            );
        }

        $productModel->setParent($parentProductModel);
    }

    /**
     * Updates the family variant of the family variant of the product model.
     *
     * @param ProductModelInterface $productModel
     * @param string                $familyVariantCode
     *
     * @throws InvalidPropertyException
     */
    private function updateFamilyVariant(ProductModelInterface $productModel, string $familyVariantCode): void
    {
        if (null !== $productModel->getFamilyVariant() &&
            $familyVariantCode !== $productModel->getFamilyVariant()->getCode()
        ) {
            throw ImmutablePropertyException::immutableProperty(
                'family_variant',
                $familyVariantCode,
                static::class
            );
        }

        $parent = $productModel->getParent();
        if (null !== $parent && $familyVariantCode !== $parent->getFamilyVariant()->getCode()) {
            throw ImmutablePropertyException::immutableProperty(
                'family_variant',
                $familyVariantCode,
                static::class
            );
        }

        if (null === $familyVariant = $this->familyVariantRepository->findOneByIdentifier($familyVariantCode)) {
            throw InvalidPropertyException::validEntityCodeExpected(
                'family_variant',
                'family variant code',
                'The family variant does not exist',
                static::class,
                $familyVariantCode
            );
        }

        $productModel->setFamilyVariant($familyVariant);
    }
}
