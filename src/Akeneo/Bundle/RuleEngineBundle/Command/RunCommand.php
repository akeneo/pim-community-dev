<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Bundle\RuleEngineBundle\Command;

use Akeneo\Bundle\RuleEngineBundle\Event\RuleEvents;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Bundle\RuleEngineBundle\Runner\BulkDryRunnerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\ChainUserProvider;

/**
 * Command to run a rule
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class RunCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('akeneo:rule:run')
            ->addArgument('code', InputArgument::OPTIONAL, 'Code of the rule to run')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Dry run')
            ->addOption('stop-on-error', null, InputOption::VALUE_NONE, 'Stop rules execution on error')
            ->addOption(
                'username',
                null,
                InputOption::VALUE_REQUIRED,
                'Username of the user to notify once rule(s) executed.'
            )
            ->setDescription('Runs all the rules or only one if a code is provided.')
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $code = $input->hasArgument('code') ? $input->getArgument('code') : null;
        $username = $input->getOption('username') ?: null;
        $stopOnError = $input->getOption('stop-on-error') ?: false;
        $dryRun = $input->getOption('dry-run') ?: false;

        $this->setUserInToken($username);

        $message = $dryRun ? 'Dry running rules...' : 'Running rules...';
        $output->writeln($message);

        $rules = $this->getRulesToRun($code);

        $progressBar = new ProgressBar($output, count($rules));

        $this->getContainer()
            ->get('event_dispatcher')
            ->addListener(
                RuleEvents::POST_EXECUTE,
                function () use ($progressBar) {
                    $progressBar->advance();
                }
            );

        $this->runRules($rules, $dryRun, $stopOnError);

        $progressBar->finish();
    }

    /**
     * @param RuleDefinitionInterface[] $rules
     * @param bool                      $dryRun
     * @param bool                      $stopOnError
     *
     * @throws \Exception
     */
    protected function runRules(array $rules, $dryRun, $stopOnError)
    {
        $chainedRunner = $this->getRuleRunner($stopOnError);

        $dryRun ? $chainedRunner->dryRunAll($rules) : $chainedRunner->runAll($rules);
    }

    /**
     * @param string $ruleCode
     *
     * @return RuleDefinitionInterface[]
     */
    protected function getRulesToRun($ruleCode)
    {
        $repository = $this->getRuleDefinitionRepository();

        if (null !== $ruleCode) {
            $rules = $repository->findBy(
                ['code' => explode(',', $ruleCode)],
                ['priority' => 'DESC']
            );

            if (empty($rules)) {
                throw new \InvalidArgumentException(sprintf('The rule(s) %s does not exists', $ruleCode));
            }
        } else {
            $rules = $repository->findAllOrderedByPriority();
        }

        return $rules;
    }

    /**
     * @param string $username
     */
    protected function setUserInToken($username)
    {
        if (empty($username)) {
            return;
        }

        $user = $this->getUserProvider()->loadUserByUsername($username);
        $token = new UsernamePasswordToken($user, null, 'main');
        $this->getTokenStorage()->setToken($token);
    }

    /**
     * @return ChainUserProvider
     */
    protected function getUserProvider()
    {
        return $this->getContainer()->get('security.user.provider.concrete.chain_provider');
    }

    /**
     * @return TokenStorageInterface
     */
    protected function getTokenStorage()
    {
        return $this->getContainer()->get('security.token_storage');
    }

    /**
     * @return RuleDefinitionRepositoryInterface
     */
    protected function getRuleDefinitionRepository()
    {
        return $this->getContainer()->get('akeneo_rule_engine.repository.rule_definition');
    }

    /**
     * @param bool $stopOnError
     *
     * @return BulkDryRunnerInterface
     */
    protected function getRuleRunner($stopOnError)
    {
        if ($stopOnError) {
            return $this->getContainer()->get('akeneo_rule_engine.runner.strict_chained');
        }

        return $this->getContainer()->get('akeneo_rule_engine.runner.chained');
    }

    /**
     * @return EventDispatcherInterface
     */
    protected function getEventDispatcher()
    {
        return $this->getContainer()->get('event_dispatcher');
    }
}
