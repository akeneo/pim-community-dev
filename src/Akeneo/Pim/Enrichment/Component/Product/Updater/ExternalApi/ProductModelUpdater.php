<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\ImmutablePropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;

/**
 * @author    Soulet Olivier <olivier.soulet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelUpdater implements ObjectUpdaterInterface
{
    /** @var ObjectUpdaterInterface */
    private $productModelUpdater;

    /**
     * @param ObjectUpdaterInterface $productModelUpdater
     */
    public function __construct(ObjectUpdaterInterface $productModelUpdater)
    {
        $this->productModelUpdater = $productModelUpdater;
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

        if (null !== $productModel->getParent() && array_key_exists('parent', $data) && null === $data['parent']) {
            throw ImmutablePropertyException::immutableProperty(
                'parent',
                'NULL',
                ProductModelInterface::class
            );
        }

        if (array_key_exists('family_variant', $data) && null === $data['family_variant']) {
            throw InvalidPropertyException::valueNotEmptyExpected(
                'family_variant',
                ProductModelInterface::class
            );
        }

        $checkFamilyCode = false;
        $familyCode = null;
        if (array_key_exists('family', $data)) {
            $familyCode = $data['family'];
            unset($data['family']);
            $checkFamilyCode = true;
        }

        $this->productModelUpdater->update($productModel, $data, $options);

        if (true === $checkFamilyCode) {
            $this->validateFamilyCode($familyCode, $productModel);
        }

        return $this;
    }

    /**
     * @throws PropertyException
     */
    private function validateFamilyCode($familyCode, ProductModelInterface $productModel): void
    {
        if (null !== $productModel->getId()) {
            if (!is_string($familyCode) || empty($familyCode) || $productModel->getFamily()->getCode() !== $familyCode) {
                throw ImmutablePropertyException::immutableProperty(
                    'family',
                    is_scalar($familyCode) ? $familyCode : gettype($familyCode),
                    ProductModelInterface::class
                );
            }
        }

        if (null === $familyCode || '' === $familyCode) {
            throw InvalidPropertyException::valueNotEmptyExpected('family', ProductModelInterface::class);
        }

        if (!is_string($familyCode)) {
            throw InvalidPropertyTypeException::stringExpected('family', ProductModelInterface::class, $familyCode);
        }

        if (null !== $productModel->getFamilyVariant() && $familyCode !== $productModel->getFamily()->getCode()) {
            throw InvalidPropertyException::expected(
                sprintf(
                    'The family "%s" does not match the family of the variant "%s".',
                    $familyCode,
                    $productModel->getFamilyVariant()->getCode()
                ),
                ProductModelInterface::class
            );
        }
    }
}
