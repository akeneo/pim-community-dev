<?php
declare(strict_types=1);

namespace Pim\Component\Catalog\Updater\Setter;

use Akeneo\Component\StorageUtils\Exception\ImmutablePropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Model\VariantProductInterface;

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
        if (!$product instanceof VariantProductInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($product),
                VariantProductInterface::class
            );
        }

        // TODO: This is to be removed in PIM-6350.
        if (null !== $product->getParent() && $data !== $product->getParent()->getCode()) {
            throw ImmutablePropertyException::immutableProperty($field, $data, static::class);
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

        $product->setParent($parent);
        $product->setFamilyVariant($familyVariant);
        if (null === $product->getFamily()) {
            $product->setFamily($familyVariant->getFamily());
        }
    }
}
