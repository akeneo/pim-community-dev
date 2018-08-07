<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ConnectorBundle\Launcher;

use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Aims to launch job that is authenticated with a username.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AuthenticatedJobLauncher implements JobLauncherInterface
{
    /** @var JobLauncherInterface */
    protected $jobLauncher;

    public function __construct(JobLauncherInterface $jobLauncher)
    {
        $this->jobLauncher = $jobLauncher;
    }

    /**
     * {@inheritdoc}
     */
    public function launch(JobInstance $jobInstance, UserInterface $user, array $configuration = []) : JobExecution
    {
        $configuration['is_user_authenticated'] = true;

        return $this->jobLauncher->launch($jobInstance, $user, $configuration);
    }
}
