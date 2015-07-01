<?php

namespace Pim\Component\Catalog\Updater\Remover;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Bundle\CatalogBundle\Exception\InvalidArgumentException;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Remove one or several groups to a product
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupFieldRemover extends AbstractFieldRemover
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
     * Expected data input format : ["group_code", "another_group_code"]
     */
    public function removeFieldData(ProductInterface $product, $field, $data, array $options = [])
    {
        $this->checkData($field, $data);

        $groups = [];
        foreach ($data as $groupCode) {
            $group = $this->groupRepository->findOneByIdentifier($groupCode);

            if (null === $group) {
                throw InvalidArgumentException::expected(
                    $field,
                    'existing group code',
                    'remover',
                    'groups',
                    $groupCode
                );
            }
            if ($group->getType()->isVariant()) {
                throw InvalidArgumentException::expected(
                    $field,
                    'non variant group code',
                    'remover',
                    'groups',
                    $groupCode
                );
            }
            $groups[] = $group;
        }

        foreach ($groups as $group) {
            $product->removeGroup($group);
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
                'remover',
                'groups',
                gettype($data)
            );
        }

        foreach ($data as $key => $value) {
            if (!is_string($value)) {
                throw InvalidArgumentException::arrayStringValueExpected(
                    $field,
                    $key,
                    'remover',
                    'groups',
                    gettype($value)
                );
            }
        }
    }
}
