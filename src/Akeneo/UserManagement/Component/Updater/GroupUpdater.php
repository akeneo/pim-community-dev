<?php

namespace Akeneo\UserManagement\Component\Updater;

use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Doctrine\Common\Util\ClassUtils;

/**
 * Updates a group
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupUpdater implements ObjectUpdaterInterface
{
    /**
     * {@inheritdoc}
     *
     * Expected input format :
     * {
     *     'name': 'IT support',
     * }
     */
    public function update($group, array $data, array $options = [])
    {
        if (!$group instanceof GroupInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($group),
                GroupInterface::class
            );
        }

        foreach ($data as $field => $value) {
            $this->setData($group, $field, $value);
        }

        return $this;
    }

    /**
     * @param GroupInterface $group
     * @param string         $field
     * @param mixed          $data
     *
     * @throws \InvalidArgumentException
     */
    protected function setData(GroupInterface $group, $field, $data)
    {
        switch ($field) {
            case 'name':
                $group->setName($data);
                break;
        }
    }
}
