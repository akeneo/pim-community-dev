<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\ExternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\ImmutablePropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
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

        if (array_key_exists('family', $data)) {
            $familyCode = $data['family'];
            unset($data['family']);
        }

        $this->productModelUpdater->update($productModel, $data, $options);

        if (isset($familyCode)) {
            $this->validateFamilyCode($familyCode, $productModel);
        }

        return $this;
    }

    /**
     * Checks that the provided family code matches the family variant code
     */
    private function validateFamilyCode($familyCode, ProductModelInterface $productModel): void
    {
        if (!is_string($familyCode)) {
            throw InvalidPropertyTypeException::stringExpected('family', ProductModelInterface::class, $familyCode);
        }

        if (null === $productModel->getFamily()) {
            return;
        }

        if ($familyCode !== $productModel->getFamily()->getCode()) {
            throw InvalidPropertyException::expected(
                sprintf(
                    'The family variant "%s" is not a variant of the family "%s".',
                    $productModel->getFamilyVariant()->getCode(),
                    $familyCode
                ),
                ProductModelInterface::class
            );
        }
    }
}
