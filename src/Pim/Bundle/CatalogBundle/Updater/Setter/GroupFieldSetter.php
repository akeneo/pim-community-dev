<?php

namespace Pim\Bundle\CatalogBundle\Updater\Setter;

use Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Updater\Setter\FieldSetterInterface;

/**
 * Sets the group field
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupFieldSetter extends AbstractFieldSetter
{
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
     * Expected data input format : ["group_code"]
     */
    public function setFieldData(ProductInterface $product, $field, $data, array $options = [])
    {
        $this->checkData($field, $data);

        $groups = [];
        foreach ($data as $groupCode) {
            $group = $this->groupRepository->findOneByIdentifier($groupCode);

            if (null === $group) {
                throw InvalidArgumentException::expected(
                    $field,
                    'existing group code',
                    'setter',
                    'group',
                    $groupCode
                );
            } elseif ($group->getType()->isVariant()) {
                throw InvalidArgumentException::expected(
                    $field,
                    'non variant group code',
                    'setter',
                    'group',
                    $groupCode
                );
            } else {
                $groups[] = $group;
            }
        }

        $oldGroups = $product->getGroups();
        foreach ($oldGroups as $group) {
            if (!$group->getType()->isVariant()) {
                $product->removeGroup($group);
            }
        }

        foreach ($groups as $group) {
            $product->addGroup($group);
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
        if (!is_array($data)) {
            throw InvalidArgumentException::arrayExpected(
                $field,
                'setter',
                'group',
                gettype($data)
            );
        }

        foreach ($data as $key => $value) {
            if (!is_string($value)) {
                throw InvalidArgumentException::arrayStringKeyExpected(
                    $field,
                    $key,
                    'setter',
                    'group',
                    gettype($value)
                );
            }
        }
    }
}
