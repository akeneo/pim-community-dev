<?php

namespace Akeneo\Pim\Structure\Component\Updater;

use Akeneo\Pim\Structure\Component\Model\GroupTypeInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;

/**
 * Updates a group type
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupTypeUpdater implements ObjectUpdaterInterface
{
    /**
     * {@inheritdoc}
     *
     * Expected input format :
     * [
     *     'code'       => 'variant',
     *     'label'      => [
     *         'en_US' => 'variant',
     *         'fr_FR' => 'variantes',
     *     ]
     * ]
     */
    public function update($groupType, array $data, array $options = [])
    {
        if (!$groupType instanceof GroupTypeInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($groupType),
                GroupTypeInterface::class
            );
        }

        foreach ($data as $field => $value) {
            $this->setData($groupType, $field, $value);
        }

        return $this;
    }

    /**
     * @param GroupTypeInterface $groupType
     * @param string             $field
     * @param mixed              $data
     */
    protected function setData(GroupTypeInterface $groupType, $field, $data)
    {
        switch ($field) {
          case 'code':
            $groupType->setCode($data);
            break;
          case 'labels':
          case 'label':
            foreach ($data as $locale => $label) {
                $groupType->setLocale($locale);
                $groupType->setLabel($label);
            }
            break;
        }
    }
}
