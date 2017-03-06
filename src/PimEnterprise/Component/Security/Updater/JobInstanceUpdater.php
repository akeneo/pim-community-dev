<?php

namespace PimEnterprise\Component\Security\Updater;

use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\ORM\EntityRepository;
use PimEnterprise\Bundle\SecurityBundle\Manager\JobProfileAccessManager;

/**
 * Update a job instance
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceUpdater implements ObjectUpdaterInterface
{
    /** @var ObjectUpdaterInterface */
    protected $jobInstanceUpdater;

    /** @var JobProfileAccessManager */
    protected $accessManager;

    /** @var EntityRepository */
    protected $userGroupRepository;

    /**
     * @param ObjectUpdaterInterface  $jobInstanceUpdater
     * @param JobProfileAccessManager $accessManager
     * @param EntityRepository        $userGroupRepository
     */
    public function __construct(
        ObjectUpdaterInterface $jobInstanceUpdater,
        JobProfileAccessManager $accessManager,
        EntityRepository $userGroupRepository
    ) {
        $this->jobInstanceUpdater  = $jobInstanceUpdater;
        $this->accessManager       = $accessManager;
        $this->userGroupRepository = $userGroupRepository;
    }

    /**
     * {@inheritdoc}
     *
     * @param JobInstance $jobInstance
     */
    public function update($jobInstance, array $data, array $options = [])
    {
        $this->jobInstanceUpdater->update($jobInstance, $data, $options);

        foreach ($data as $field => $value) {
            $this->setData($jobInstance, $field, $value);
        }
    }

    /**
     * @param JobInstance $jobInstance
     * @param string      $field
     * @param mixed       $data
     */
    protected function setData(JobInstance $jobInstance, $field, $data)
    {
        switch ($field) {
            case 'permissions':
                $this->accessManager->setAccess(
                    $jobInstance,
                    $this->getGroups($data['execute']),
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
