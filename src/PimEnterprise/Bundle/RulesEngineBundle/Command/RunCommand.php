<?php

namespace Pim\Bundle\RulesEngineBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Monolog\Handler\StreamHandler;
use Pim\Bundle\VersioningBundle\Model\Version;

class RunCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:rules:run')
            ->addArgument('code', InputArgument::REQUIRED, 'Rule instance code');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // get rule instance
        $code = $input->getArgument('code');
        $repo = $this->getContainer()->get('pim_rules_engine.repository.rule_instance');
        $ruleInstance = $repo->findOneByCode($code);

        // load/prepare the rule
        $loaderRegistry = $this->getContainer()->get('pim_rules_engine.loader.chained');
        $businessRule = $loaderRegistry->load($ruleInstance);

        // run the rule
        $runnerRegistry = $this->getContainer()->get('pim_rules_engine.runner.chained');
        $runnerRegistry->run($businessRule);
    }
}
