<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\TeamworkAssistant\Factory;

use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class ProjectStatusFactory implements ProjectStatusFactoryInterface
{
    /** @var string */
    protected $projectStatusClassName;

    /**
     * @param string $projectStatusClassName
     */
    public function __construct($projectStatusClassName)
    {
        $this->projectStatusClassName = $projectStatusClassName;
    }

    /**
     * {@inheritdoc}
     */
    public function create(ProjectInterface $project, UserInterface $user)
    {
        $projectStatus = new $this->projectStatusClassName();
        $projectStatus->setUser($user);
        $projectStatus->setProject($project);

        return $projectStatus;
    }
}
