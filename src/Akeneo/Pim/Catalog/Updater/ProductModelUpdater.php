<?php

declare(strict_types=1);

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Exception\ImmutablePropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\ProductModelInterface;

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

    /**
     * @param PropertySetterInterface               $propertySetter
     * @param ObjectUpdaterInterface                $valuesUpdater
     * @param IdentifiableObjectRepositoryInterface $familyVariantRepository
     * @param IdentifiableObjectRepositoryInterface $productModelRepository
     * @param array                                 $supportedFields
     * @param array                                 $ignoredFields
     */
    public function __construct(
        PropertySetterInterface $propertySetter,
        ObjectUpdaterInterface $valuesUpdater,
        IdentifiableObjectRepositoryInterface $familyVariantRepository,
        IdentifiableObjectRepositoryInterface $productModelRepository,
        array $supportedFields,
        array $ignoredFields
    ) {
        $this->propertySetter = $propertySetter;
        $this->valuesUpdater = $valuesUpdater;
        $this->familyVariantRepository = $familyVariantRepository;
        $this->productModelRepository = $productModelRepository;
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

        if (array_key_exists('parent', $data)) {
            $this->updateParentAndFamily($productModel, $data);
            unset($data['parent']);
        }

        foreach ($data as $code => $value) {
            $this->setData($productModel, $code, $value, $options);
        }

        return $this;
    }

    /**
     * @param ProductModelInterface $productModel
     * @param                       $field
     * @param                       $data
     * @param array                 $options
     */
    protected function setData(ProductModelInterface $productModel, $field, $data, $options = [])
    {
        switch ($field) {
            case 'values':
                $this->valuesUpdater->update($productModel, $data, $options);
                break;
            case 'code':
                $this->validateScalar($field, $data);
                $productModel->setCode($data);
                break;
            case 'family_variant':
                $this->validateScalar($field, $data);
                $this->updateFamilyVariant($productModel, $data);
                break;
            case 'categories':
                $this->validateScalarArray($field, $data);
                $this->propertySetter->setData($productModel, $field, $data);
                break;
            default:
                if (!in_array($field, $this->ignoredFields)) {
                    throw UnknownPropertyException::unknownProperty($field);
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
    private function validateScalar($field, $data)
    {
        if (null !== $data && !is_scalar($data)) {
            throw InvalidPropertyTypeException::scalarExpected($field, static::class, $data);
        }
    }

    /**
     * Validate that it is an array with scalar values.
     *
     * @param string $field
     * @param mixed $data
     *
     * @throws InvalidPropertyTypeException
     */
    private function validateScalarArray($field, $data)
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
            $parent = $productModel->getParent();
            if (null !== $parent) {
                $productModel->setFamilyVariant($parent->getFamilyVariant());
            }
        }
    }

    /**
     * Updates the parent of the product model.
     * If the product model is a root one, it cannot have a parent, so an
     * exception will be thrown.
     *
     * @param ProductModelInterface $productModel
     * @param string|null                $parentCode
     *
     * @throws ImmutablePropertyException
     * @throws InvalidPropertyException
     */
    private function updateParent(ProductModelInterface $productModel, ?string $parentCode): void
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
     * @throws ImmutablePropertyException
     * @throws InvalidPropertyException
     */
    private function updateFamilyVariant(ProductModelInterface $productModel, string $familyVariantCode): void
    {
        if (empty($familyVariantCode) && null !== $productModel->getFamilyVariant()) {
            return;
        }

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
            throw InvalidPropertyException::expected(
                sprintf(
                    'The parent is not a product model of the family variant "%s" but belongs to the family "%s".',
                    $familyVariantCode,
                    $parent->getFamilyVariant()->getCode()
                ),
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
