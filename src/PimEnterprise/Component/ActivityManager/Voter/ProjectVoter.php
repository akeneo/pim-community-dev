<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Voter;

use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use PimEnterprise\Component\ActivityManager\Repository\UserRepositoryInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AbstractVoter;

/**
 * Project voter, allow to know if a user has own and/or contribute access to a project.
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectVoter extends AbstractVoter
{
    const OWN = 'OWN';
    const CONTRIBUTE = 'CONTRIBUTE';

    /** @var UserRepositoryInterface */
    private $userRepository;

    /**
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedClasses()
    {
        return [ProjectInterface::class];
    }

    /**
     * {@inheritdoc}
     */
    protected function getSupportedAttributes()
    {
        return [self::OWN, self::CONTRIBUTE];
    }

    /**
     * {@inheritdoc}
     *
     * @param ProjectInterface   $project
     * @param UserInterface|null $user
     */
    protected function isGranted($attribute, $project, $user = null)
    {
        if (null === $user) {
            return false;
        }

        switch ($attribute) {
            case self::OWN:
                return $project->getOwner()->getId() === $user->getId();
            case self::CONTRIBUTE:
                return $this->userRepository->isProjectContributor($project, $user);
        }
    }
}
