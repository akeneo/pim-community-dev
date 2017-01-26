<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Model;

use PimEnterprise\Bundle\UserBundle\Entity\UserInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
interface ProjectStatusInterface
{
    /**
     * {@inheritdoc}
     */
    public function getId();

    /**
     * @return UserInterface
     */
    public function getUser();

    /**
     * @param UserInterface $user
     */
    public function setUser(UserInterface $user);

    /**
     * @return ProjectInterface
     */
    public function getProject();

    /**
     * @param ProjectInterface $project
     */
    public function setProject(ProjectInterface $project);

    /**
     * @return bool
     */
    public function isComplete();

    /**
     * @param bool $isComplete
     */
    public function setIsComplete($isComplete);
}
