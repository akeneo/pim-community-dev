<?php

namespace Pim\Component\Catalog\Updater\Setter;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Sets the variant group field
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupFieldSetter extends AbstractFieldSetter
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $groupRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $groupRepository
     * @param array                                 $supportedFields
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $groupRepository,
        array $supportedFields
    ) {
        $this->groupRepository = $groupRepository;
        $this->supportedFields = $supportedFields;
    }

    /**
     * {@inheritdoc}
     *
     * Expected data input format : "variant_group_code"
     */
    public function setFieldData($product, $field, $data, array $options = [])
    {
        if (!$product instanceof ProductInterface) {
            throw InvalidObjectException::objectExpected($product, ProductInterface::class);
        }

        $this->checkData($field, $data);

        if (null !== $data) {
            $variantGroup = $this->groupRepository->findOneByIdentifier($data);
            if (null === $variantGroup) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    $field,
                    'variant group code',
                    'The variant group does not exist',
                    static::class,
                    $data
                );
            }

            if (!$variantGroup->getType()->isVariant()) {
                throw InvalidPropertyException::validGroupExpected(
                    $field,
                    'Cannot process group, only variant groups are supported',
                    static::class,
                    $data
                );
            }
        }

        $existingGroups = $product->getGroups();
        foreach ($existingGroups as $group) {
            if ($group->getType()->isVariant()) {
                $product->removeGroup($group);
            }
        }

        if (null !== $data) {
            $product->addGroup($variantGroup);
        }
    }

    /**
     * Check if data are valid
     *
     * @param string $field
     * @param mixed  $data
     *
     * @throws InvalidPropertyTypeException
     */
    protected function checkData($field, $data)
    {
        if (!is_string($data) && null !== $data) {
            throw InvalidPropertyTypeException::stringExpected(
                $field,
                static::class,
                $data
            );
        }
    }
}
