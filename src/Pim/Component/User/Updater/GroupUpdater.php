<?php

namespace Pim\Component\User\Updater;

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\UserBundle\Entity\Group;

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
        if (!$group instanceof Group) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Oro\Bundle\UserBundle\Entity\Group", "%s" provided.',
                    ClassUtils::getClass($group)
                )
            );
        }

        foreach ($data as $field => $value) {
            $this->setData($group, $field, $value);
        }

        return $this;
    }

    /**
     * @param Group $group
     * @param string        $field
     * @param mixed         $data
     *
     * @throws \InvalidArgumentException
     */
    protected function setData(Group $group, $field, $data)
    {
        switch ($field) {
            case 'name':
                $group->setName($data);
                break;
        }
    }
}
