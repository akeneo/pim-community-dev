<?php

namespace PimEnterprise\Component\Security\Updater;

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\ORM\EntityRepository;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use PimEnterprise\Bundle\SecurityBundle\Manager\AttributeGroupAccessManager;

/**
 * Update an attribute group
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupUpdater implements ObjectUpdaterInterface
{
    /** @var ObjectUpdaterInterface */
    protected $attributeGroupUpdater;

    /** @var AttributeGroupAccessManager */
    protected $accessManager;

    /** @var EntityRepository */
    protected $userGroupRepository;

    /**
     * @param ObjectUpdaterInterface  $attributeGroupUpdater
     * @param AttributeGroupAccessManager $accessManager
     * @param EntityRepository        $userGroupRepository
     */
    public function __construct(
        ObjectUpdaterInterface $attributeGroupUpdater,
        AttributeGroupAccessManager $accessManager,
        EntityRepository $userGroupRepository
    ) {
        $this->attributeGroupUpdater  = $attributeGroupUpdater;
        $this->accessManager       = $accessManager;
        $this->userGroupRepository = $userGroupRepository;
    }

    /**
     * {@inheritdoc}
     *
     * @param AttributeGroup $attributeGroup
     */
    public function update($attributeGroup, array $data, array $options = [])
    {
        $this->attributeGroupUpdater->update($attributeGroup, $data, $options);

        foreach ($data as $field => $value) {
            $this->setData($attributeGroup, $field, $value);
        }
    }

    /**
     * @param AttributeGroupInterface $attributeGroup
     * @param string                  $field
     * @param mixed                   $data
     */
    protected function setData(AttributeGroupInterface $attributeGroup, $field, $data)
    {
        switch ($field) {
            case 'permissions':
                $this->accessManager->setAccess(
                    $attributeGroup,
                    $this->getGroups($data['view']),
                    $this->getGroups($data['edit'])
                );
                break;
        }
    }

    /**
     * Get corresponding groups for given names
     *
     * @param array $groupsNames
     *
     * @return array
     */
    protected function getGroups($groupsNames)
    {
        return array_filter($this->userGroupRepository->findAll(), function ($group) use ($groupsNames) {
            return in_array($group->getName(), $groupsNames);
        });
    }
}
