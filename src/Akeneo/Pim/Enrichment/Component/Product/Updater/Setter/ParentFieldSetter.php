<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Setter;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\ImmutablePropertyException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Util\ClassUtils;

/**
 * Set the parent to a variant product
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ParentFieldSetter extends AbstractFieldSetter
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $productModelRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $productModelRepository
     * @param string[]                              $supportedFields
     */
    public function __construct(IdentifiableObjectRepositoryInterface $productModelRepository, array $supportedFields)
    {
        $this->productModelRepository = $productModelRepository;
        $this->supportedFields = $supportedFields;
    }

    /**
     * {@inheritdoc}
     */
    public function setFieldData($product, $field, $data, array $options = []): void
    {
        if (!$product instanceof ProductInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($product),
                ProductInterface::class
            );
        }

        if ($product->isVariant() && null === $data) {
            throw ImmutablePropertyException::immutableProperty($field, $data, static::class);
        }

        if (null === $data) {
            return;
        }

        if (null === $parent = $this->productModelRepository->findOneByIdentifier($data)) {
            throw InvalidPropertyException::validEntityCodeExpected(
                $field,
                'parent code',
                'The parent product model does not exist',
                static::class,
                $data
            );
        }

        $familyVariant = $parent->getFamilyVariant();

        if (null !== $familyVariant && $product->isVariant() && $product->getFamilyVariant() !== $familyVariant) {
            throw InvalidPropertyException::expected(
                sprintf(
                    'New parent "%s" of variant product "%s" must have the same family variant "%s" than the previous parent',
                    $parent->getCode(),
                    $product->getIdentifier(),
                    $product->getFamilyVariant()->getCode()
                ),
                static::class
            );
        }

        $product->setParent($parent);
        $product->setFamilyVariant($familyVariant);
        if (null === $product->getFamily()) {
            $product->setFamily($familyVariant->getFamily());
        }
    }
}
