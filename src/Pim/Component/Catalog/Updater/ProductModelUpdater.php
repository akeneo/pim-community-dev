<?php

namespace Pim\Component\Catalog\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
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

        foreach ($data as $code => $value) {
            if ('values' === $code) {
                $this->valuesUpdater->update($productModel, $value, $options);
            } elseif ('code' === $code) {
                $productModel->setCode($value);
            } elseif ('parent' === $code) {
                if (!empty($value)) {
                    if (null === $parentProductModel = $this->productModelRepository->findOneByIdentifier($value)) {
                        throw InvalidPropertyException::validEntityCodeExpected(
                            'parent',
                            'parent code',
                            'The product model does not exist',
                            static::class,
                            $value
                        );
                    }

                    $productModel->setParent($parentProductModel);
                }
            } elseif ('family_variant' === $code) {
                if (null === $familyVariant = $this->familyVariantRepository->findOneByIdentifier($value)) {
                    throw InvalidPropertyException::validEntityCodeExpected(
                        'family_variant',
                        'family variant code',
                        'The family variant does not exist',
                        static::class,
                        $value
                    );
                }

                $productModel->setFamilyVariant($familyVariant);
            } elseif (in_array($code, $this->supportedFields)) {
                $this->propertySetter->setData($productModel, $code, $value);
            } elseif (!in_array($code, $this->ignoredFields)) {
                throw UnknownPropertyException::unknownProperty($code);
            }
        }

        return $this;
    }
}
