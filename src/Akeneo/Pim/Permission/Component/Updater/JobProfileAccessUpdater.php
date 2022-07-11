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

use Akeneo\Pim\Permission\Component\Model\JobProfileAccessInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;

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
        $this->jobRepository = $jobRepository;
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
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($jobProfileAccess),
                JobProfileAccessInterface::class
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
     * @throws InvalidPropertyException
     */
    protected function setData(JobProfileAccessInterface $jobProfileAccess, $field, $data)
    {
        switch ($field) {
            case 'job_profile':
                $jobProfile = $this->jobRepository->findOneByIdentifier($data);
                if (null === $jobProfile) {
                    throw InvalidPropertyException::validEntityCodeExpected(
                        'job_profile',
                        'job profile code',
                        'The job profile does not exist',
                        static::class,
                        $data
                    );
                }
            $jobProfileAccess->setJobProfile($jobProfile);
            break;
            case 'user_group':
                $group = $this->groupRepository->findOneByIdentifier($data);
                if (null === $group) {
                    throw InvalidPropertyException::validEntityCodeExpected(
                        'user_group',
                        'group code',
                        'The group does not exist',
                        static::class,
                        $data
                    );
                }
            $jobProfileAccess->setUserGroup($group);
            break;
            case 'execute_job_profile':
                $jobProfileAccess->setExecuteJobProfile($data);
                break;
            case 'edit_job_profile':
                if (true === $data) {
                    $jobProfileAccess->setExecuteJobProfile($data);
                }

            $jobProfileAccess->setEditJobProfile($data);
            break;
        }
    }
}
