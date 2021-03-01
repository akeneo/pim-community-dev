<?php

namespace Akeneo\UserManagement\Component\Updater;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\RoleInterface;
use Doctrine\Common\Util\ClassUtils;

/**
 * Updates a role
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RoleUpdater implements ObjectUpdaterInterface
{
    /**
     * {@inheritdoc}
     *
     * Expected input format :
     * {
     *     'role': 'ROLE_ADMINISTRATOR',
     *     'label': 'Administrator',
     * }
     */
    public function update($role, array $data, array $options = []): self
    {
        if (!$role instanceof RoleInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($role),
                RoleInterface::class
            );
        }

        foreach ($data as $field => $value) {
            $this->checkDataType($field, $value);
            $this->setData($role, $field, $value);
        }

        return $this;
    }

    /**
     * @param RoleInterface $role
     * @param string        $field
     * @param mixed         $data
     *
     * @throws UnknownPropertyException
     */
    protected function setData(RoleInterface $role, string $field, $data)
    {
        switch ($field) {
            case 'role':
                $role->setRole($data);
                break;
            case 'label':
                $role->setLabel($data);
                break;
            default:
                throw UnknownPropertyException::unknownProperty($field);
        }
    }

    protected function checkDataType(string $field, $data): void
    {
        if (\in_array($field, ['role', 'label']) && null !== $data && !\is_string($data)) {
            throw InvalidPropertyTypeException::stringExpected($field, static::class, $data);
        }
    }
}
