<?php

namespace Pim\Component\Catalog\Updater\Remover;

use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

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
    public function removeFieldData($product, $field, $data, array $options = [])
    {
        $this->checkData($field, $data);

        $groups = [];
        foreach ($data as $groupCode) {
            $group = $this->groupRepository->findOneByIdentifier($groupCode);

            if (null === $group) {
                throw InvalidPropertyException::validEntityCodeExpected(
                    $field,
                    'group code',
                    'The group does not exist',
                    static::class,
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
     *
     * @throws InvalidPropertyTypeException
     */
    protected function checkData($field, $data)
    {
        if (!is_array($data)) {
            throw InvalidPropertyTypeException::arrayExpected(
                $field,
                static::class,
                $data
            );
        }

        foreach ($data as $key => $value) {
            if (!is_string($value)) {
                throw InvalidPropertyTypeException::validArrayStructureExpected(
                    $field,
                    sprintf('one of the group codes is not a string, "%s" given', gettype($value)),
                    static::class,
                    $data
                );
            }
        }
    }
}
