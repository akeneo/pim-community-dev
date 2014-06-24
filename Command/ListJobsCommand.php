<?php

namespace Akeneo\Bundle\BatchBundle\Command;

use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Lists active batch jobs
 * 
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ListJobsCommand extends ContainerAwareCommand
{
    /**
     * @staticvar string Option used to list all jobs
     */
    const LIST_ALL = 'all';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('akeneo:batch:list-jobs')
            ->addOption(
                'type',
                't',
                InputOption::VALUE_REQUIRED,
                'The type of jobs to list (import|export|all)',
                static::LIST_ALL
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $criteria = [];
        $type = $input->getOption('type');
        if (static::LIST_ALL !== $type) {
            $criteria['type'] = $type;
        }
        $jobs = $this->getJobManager()->getRepository('AkeneoBatchBundle:JobInstance')
            ->findBy($criteria, ['code' => 'desc']);
        foreach ($jobs as $job) {
            if (static::LIST_ALL === $type) {
                $output->writeln(sprintf("%s\t%s",$job->getType(), $job->getCode()));
            } else {
                $output->writeln($job->getCode());
            }
        }
    }

    /**
     * @return EntityManager
     */
    protected function getJobManager()
    {
        return $this->getContainer()->get('akeneo_batch.job_repository')->getJobManager();
    }
}
