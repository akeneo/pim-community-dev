<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Component\Updater;

use Akeneo\Pim\Permission\Component\Model\AttributeGroupAccessInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;

/**
 * Updates an Attribute Group Access
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class AttributeGroupAccessUpdater implements ObjectUpdaterInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $userGroupRepo;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeGroupRepo;

    /**
     * @param IdentifiableObjectRepositoryInterface $userGroupRepo
     * @param IdentifiableObjectRepositoryInterface $attributeGroupRepo
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $userGroupRepo,
        IdentifiableObjectRepositoryInterface $attributeGroupRepo
    ) {
        $this->userGroupRepo = $userGroupRepo;
        $this->attributeGroupRepo = $attributeGroupRepo;
    }

    /**
     * {@inheritdoc}
     *
     * Expected input format :
     * [
     *      'attribute_group'  => 'other',
     *      'user_group'      => 'IT Manager',
     *      'view_attributes' => true,
     *      'edit_attributes' => false,
     * ]
     */
    public function update($groupAccess, array $data, array $options = [])
    {
        if (!$groupAccess instanceof AttributeGroupAccessInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($groupAccess),
                AttributeGroupAccessInterface::class
            );
        }

        foreach ($data as $field => $value) {
            $this->setData($groupAccess, $field, $value);
        }

        return $this;
    }

    /**
     * @param AttributeGroupAccessInterface $groupAccess
     * @param string                        $field
     * @param mixed                         $data
     *
     * @throws InvalidPropertyException
     */
    protected function setData(AttributeGroupAccessInterface $groupAccess, $field, $data)
    {
        switch ($field) {
            case 'attribute_group':
                $attributeGroup = $this->attributeGroupRepo->findOneByIdentifier($data);
                if (null === $attributeGroup) {
                    throw InvalidPropertyException::validEntityCodeExpected(
                        'attribute_group',
                        'attribute group code',
                        'The attribute group does not exist',
                        static::class,
                        $data
                    );
                }
                $groupAccess->setAttributeGroup($attributeGroup);
                break;
            case 'user_group':
                $group = $this->userGroupRepo->findOneByIdentifier($data);
                if (null === $group) {
                    throw InvalidPropertyException::validEntityCodeExpected(
                        'user_group',
                        'group code',
                        'The group does not exist',
                        static::class,
                        $data
                    );
                }
                $groupAccess->setUserGroup($group);
                break;
            case 'view_attributes':
                $groupAccess->setViewAttributes($data);
                break;
            case 'edit_attributes':
                if (true === $data) {
                    $groupAccess->setViewAttributes($data);
                }
                $groupAccess->setEditAttributes($data);
                break;
        }
    }
}
