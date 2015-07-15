<?php

namespace Pim\Component\Catalog\Updater\Setter;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Sets the group field, for now, it handles groups and variant group, in the future, we'll separate them, we can
 * already use the VariantGroupFieldSetter
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupFieldSetter extends AbstractFieldSetter
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
                    'groups',
                    $groupCode
                );
            } else {
                $groups[] = $group;
            }
        }

        $oldGroups = $product->getGroups();
        foreach ($oldGroups as $group) {
            $product->removeGroup($group);
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
                'groups',
                gettype($data)
            );
        }

        foreach ($data as $key => $value) {
            if (!is_string($value)) {
                throw InvalidArgumentException::arrayStringValueExpected(
                    $field,
                    $key,
                    'setter',
                    'groups',
                    gettype($value)
                );
            }
        }
    }
}
