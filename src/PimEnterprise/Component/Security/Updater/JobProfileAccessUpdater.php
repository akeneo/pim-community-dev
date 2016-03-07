<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Security\Updater;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use PimEnterprise\Component\Security\Model\JobProfileAccessInterface;

/**
 * Updates a Job Profile Access
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class JobProfileAccessUpdater implements ObjectUpdaterInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $groupRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $jobRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $groupRepository
     * @param IdentifiableObjectRepositoryInterface $jobRepository
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $groupRepository,
        IdentifiableObjectRepositoryInterface $jobRepository
    ) {
        $this->groupRepository = $groupRepository;
        $this->jobRepository   = $jobRepository;
    }

    /**
     * {@inheritdoc}
     *
     * Expected input format :
     * [
     *      'job_profile'   => 'product_import',
     *      'user_group'    => 'IT Manager'
     *      'view_products' => true,
     *      'edit_products' => false,
     * ]
     */
    public function update($jobProfileAccess, array $data, array $options = [])
    {
        if (!$jobProfileAccess instanceof JobProfileAccessInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "PimEnterprise\Component\Security\Model\JobProfileAccessInterface", "%s" provided.',
                    ClassUtils::getClass($jobProfileAccess)
                )
            );
        }

        foreach ($data as $field => $value) {
            $this->setData($jobProfileAccess, $field, $value);
        }

        return $this;
    }

    /**
     * @param JobProfileAccessInterface $jobProfileAccess
     * @param string                    $field
     * @param mixed                     $data
     *
     * @throws \InvalidArgumentException
     */
    protected function setData(JobProfileAccessInterface $jobProfileAccess, $field, $data)
    {
        switch ($field) {
            case 'job_profile':
                $jobProfile = $this->jobRepository->findOneByIdentifier($data);
                if (null === $jobProfile) {
                    throw new \InvalidArgumentException(sprintf('Job Profile with "%s" code does not exist', $data));
                }
                $jobProfileAccess->setJobProfile($jobProfile);
                break;
            case 'user_group':
                $group = $this->groupRepository->findOneByIdentifier($data);
                if (null === $group) {
                    throw new \InvalidArgumentException(sprintf('Group with "%s" code does not exist', $data));
                }
                $jobProfileAccess->setUserGroup($group);
                break;
            case 'execute_job_profile':
                $jobProfileAccess->setExecuteJobProfile($data);
                break;
            case 'edit_job_profile':
                $jobProfileAccess->setEditJobProfile($data);
                break;
        }
    }
}
