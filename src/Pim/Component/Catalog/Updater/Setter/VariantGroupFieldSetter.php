<?php

namespace Pim\Component\Catalog\Updater\Setter;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

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
    public function setFieldData(ProductInterface $product, $field, $data, array $options = [])
    {
        $this->checkData($field, $data);

        if (null !== $data) {
            $variantGroup = $this->groupRepository->findOneByIdentifier($data);
            if (null === $variantGroup) {
                throw InvalidArgumentException::expected(
                    $field,
                    'existing variant group code',
                    'setter',
                    'variant_group',
                    $data
                );
            }

            if (!$variantGroup->getType()->isVariant()) {
                throw InvalidArgumentException::expected(
                    $field,
                    'variant group code',
                    'setter',
                    'variant_group',
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
     */
    protected function checkData($field, $data)
    {
        if (!is_string($data) && null !== $data) {
            throw InvalidArgumentException::stringExpected(
                $field,
                'setter',
                'variant_group',
                gettype($data)
            );
        }
    }
}
