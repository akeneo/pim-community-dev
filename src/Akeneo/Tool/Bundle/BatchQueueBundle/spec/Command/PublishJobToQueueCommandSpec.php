<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Bundle\BatchQueueBundle\Command;

use Akeneo\Tool\Bundle\BatchBundle\Job\DoctrineJobRepository;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\BatchQueue\Queue\PublishJobToQueue;
use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PublishJobToQueueCommandSpec extends ObjectBehavior
{
    function let(
        ContainerInterface $container,
        PublishJobToQueue $publishJobToQueue,
        DoctrineJobRepository $jobRepository,
        EntityManagerInterface $entityManager,
        JobInstanceRepository $jobInstanceRepository
    ) {
        $jobInstanceClass = 'Akeneo\Tool\Component\Batch\Model\JobInstance';
        $container->get('akeneo_batch_queue.queue.publish_job_to_queue')->willReturn($publishJobToQueue);
        $container->getParameter('akeneo_batch.entity.job_instance.class')->willReturn($jobInstanceClass);
        $container->get('akeneo_batch.job_repository')->willReturn($jobRepository);

        $jobRepository->getJobManager()->willReturn($entityManager);
        $entityManager->getRepository($jobInstanceClass)->willReturn($jobInstanceRepository);
    }

    function it_has_a_name()
    {
        $this->getName()->shouldReturn('akeneo:batch:publish-job-to-queue');
    }

    function it_is_a_command()
    {
        $this->shouldBeAnInstanceOf(ContainerAwareCommand::class);
    }

    public function it_publishes_a_job_to_the_job_queue(
        ContainerInterface $container,
        InputInterface $input,
        OutputInterface $output,
        Application $application,
        HelperSet $helperSet,
        InputDefinition $definition,
        PublishJobToQueue $publishJobToQueue,
        JobInstanceRepository $jobInstanceRepository,
        JobInstance $jobInstance
    ) {
        $definition->getOptions()->willReturn([]);
        $definition->getArguments()->willReturn([]);

        $application->getHelperSet()->willReturn($helperSet);
        $application->getDefinition()->willReturn($definition);

        $this->setApplication($application);
        $this->setContainer($container);

        $input->bind(Argument::any())->shouldBeCalled();
        $input->isInteractive()->shouldBeCalled();
        $input->hasArgument(Argument::any())->shouldBeCalled();
        $input->validate()->shouldBeCalled();

        $inputCode = 'the_job_instance_code';
        $inputConfig = '{"key": "data", "superkey": 50}';
        $inputNoLog = null;
        $inputUsername = 'admin';
        $inputEmail = null;

        $input->getArgument('code')->willReturn($inputCode);
        $input->getOption('config')->willReturn($inputConfig);
        $input->getOption('no-log')->willReturn($inputNoLog);
        $input->getOption('username')->willReturn($inputUsername);
        $input->getOption('email')->willReturn($inputEmail);

        $publishJobToQueue->publish(
            $inputCode,
            json_decode($inputConfig, true),
            false,
            $inputUsername,
            $inputEmail
        )->shouldBeCalled();

        $jobInstanceRepository->findOneBy(['code' => $inputCode])->willReturn($jobInstance);
        $jobInstance->getType()->willReturn('jobType');
        $jobInstance->getCode()->willReturn($inputCode);

        $output->writeln('<info>JobType the_job_instance_code has been successfully pushed into the queue.</info>')->shouldBeCalled();

        $this->run($input, $output);
    }

    public function it_throws_an_exception_if_the_config_string_is_malformed(
        ContainerInterface $container,
        InputInterface $input,
        OutputInterface $output,
        Application $application,
        HelperSet $helperSet,
        InputDefinition $definition
    ) {
        $definition->getOptions()->willReturn([]);
        $definition->getArguments()->willReturn([]);

        $application->getHelperSet()->willReturn($helperSet);
        $application->getDefinition()->willReturn($definition);

        $this->setApplication($application);
        $this->setContainer($container);

        $input->bind(Argument::any())->shouldBeCalled();
        $input->isInteractive()->shouldBeCalled();
        $input->hasArgument(Argument::any())->shouldBeCalled();
        $input->validate()->shouldBeCalled();

        $inputCode = 'the_job_instance_code';
        $inputConfig = '{{invalid_config}';
        $inputNoLog = null;
        $inputUsername = 'admin';
        $inputEmail = null;

        $input->getArgument('code')->willReturn($inputCode);
        $input->getOption('config')->willReturn($inputConfig);
        $input->getOption('no-log')->willReturn($inputNoLog);
        $input->getOption('username')->willReturn($inputUsername);
        $input->getOption('email')->willReturn($inputEmail);

        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'run', [$input, $output]
        );
    }
}
