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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Symfony\Command;

use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusHandler;
use Akeneo\Pim\Automation\FranklinInsights\Application\Configuration\Query\GetConnectionStatusQuery;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobInstanceNames;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class FetchProductsCommand extends ContainerAwareCommand
{
    public const NAME = 'pimee:franklin-insights:fetch-products';

    /** @var JobInstanceRepository */
    private $jobInstanceRepository;

    /** @var JobLauncherInterface */
    private $jobLauncher;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var GetConnectionStatusHandler */
    private $getConnectionStatusHandler;

    protected function configure(): void
    {
        $this
            ->setName(self::NAME)
            ->setDescription('Fetch products from Ask Franklin');
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->jobInstanceRepository = $this->getContainer()->get('akeneo_batch.job.job_instance_repository');
        $this->jobLauncher = $this->getContainer()->get('akeneo_batch_queue.launcher.queue_job_launcher');
        $this->tokenStorage = $this->getContainer()->get('security.token_storage');
        $this->getConnectionStatusHandler = $this->getContainer()->get(
            'akeneo.pim.automation.franklin_insights.application.configuration.query.get_connection_status_handler'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $connectionStatus = $this->getConnectionStatusHandler->handle(new GetConnectionStatusQuery(false));
        if (false === $connectionStatus->isActive()) {
            return;
        }

        /**
         * @var JobInstance $jobInstance
         */
        $jobInstance = $this->jobInstanceRepository->findOneByIdentifier(JobInstanceNames::FETCH_PRODUCTS);
        if (null === $jobInstance) {
            throw new \LogicException(
                sprintf(
                    'The job instance "%s" does not exist. Please contact your administrator.',
                    JobInstanceNames::FETCH_PRODUCTS
                )
            );
        }

        $this->jobLauncher->launch(
            $jobInstance,
            $this->getUser()
        );
    }

    private function getUser(): UserInterface
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            throw new \LogicException();
        }

        if (null === $user = $token->getUser()) {
            throw new \LogicException();
        }

        return $user;
    }
}
