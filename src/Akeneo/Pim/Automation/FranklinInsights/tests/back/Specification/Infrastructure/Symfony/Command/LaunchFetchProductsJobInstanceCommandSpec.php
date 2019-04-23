<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Symfony\Command;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Model\Read\ConnectionStatus;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobInstanceNames;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class LaunchFetchProductsJobInstanceCommandSpec extends ObjectBehavior
{
    public function let(
        ContainerInterface $container,
        JobInstanceRepository $jobInstanceRepository,
        JobLauncherInterface $jobLauncher,
        TokenStorageInterface $tokenStorage,
        GetConnectionStatusHandler $getConnectionStatusHandler,
        JobInstance $jobInstance,
        TokenInterface $token,
        UserInterface $user
    ): void {
        $jobInstanceRepository->findOneByIdentifier(JobInstanceNames::FETCH_PRODUCTS)->willReturn($jobInstance);

        $token->getUser()->willReturn($user);
        $tokenStorage->getToken()->willReturn($token);

        $container->get('akeneo_batch.job.job_instance_repository')
            ->willReturn($jobInstanceRepository);
        $container->get('akeneo_batch_queue.launcher.queue_job_launcher')
            ->willReturn($jobLauncher);
        $container->get('security.token_storage')
            ->willReturn($tokenStorage);
        $container->get(
            'akeneo.pim.automation.franklin_insights.application.configuration.query.get_connection_status_handler'
        )
            ->willReturn($getConnectionStatusHandler);


        $this->setContainer($container);
    }

    public function it_is_a_command(): void
    {
        $this->shouldBeAnInstanceOf(ContainerAwareCommand::class);
    }

    public function it_has_a_name(): void
    {
        $this->getName()->shouldReturn('pimee:franklin-insights:launch-fetch-products-job-instance');
    }

    public function it_launches_the_job_when_the_connection_is_active(
        $getConnectionStatusHandler,
        $jobLauncher,
        $jobInstance,
        $user,
        InputInterface $input,
        OutputInterface $output
    ): void {
        $connectionStatus = new ConnectionStatus(true, false, false, 0);

        $getConnectionStatusHandler->handle(Argument::any())->willReturn($connectionStatus);

        $jobLauncher->launch($jobInstance, $user)->shouldBeCalled();

        $this->run($input, $output);
    }

    public function it_stops_when_the_connection_is_inactive(
        $getConnectionStatusHandler,
        $jobLauncher,
        $jobInstance,
        $user,
        InputInterface $input,
        OutputInterface $output
    ): void {
        $connectionStatus = new ConnectionStatus(false, false, false, 0);

        $getConnectionStatusHandler->handle(Argument::any())->willReturn($connectionStatus);

        $jobLauncher->launch($jobInstance, $user)->shouldNotBeCalled();

        $this->run($input, $output);
    }
}
