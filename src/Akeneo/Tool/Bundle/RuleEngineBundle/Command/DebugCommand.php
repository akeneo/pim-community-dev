<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Bundle\RuleEngineBundle\Command;

use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to display rules information
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class DebugCommand extends Command
{
    protected static $defaultName = 'akeneo:rule:debug';

    /** @var RuleDefinitionRepositoryInterface */
    private $ruleDefinitionRepository;

    public function __construct(RuleDefinitionRepositoryInterface $ruleDefinitionRepository)
    {
        parent::__construct();

        $this->ruleDefinitionRepository = $ruleDefinitionRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addArgument('code', InputArgument::OPTIONAL, 'Code of the rule to display information')
            ->setDescription('Displays rules information.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (null !== $code = $input->getArgument('code')) {
            $this->showRule($output, $code);
        } else {
            $this->listRules($output);
        }
    }

    /**
     * @param OutputInterface $output
     * @param string          $code
     */
    protected function showRule(OutputInterface $output, $code)
    {
        $rule = $this->ruleDefinitionRepository->findOneBy(['code' => $code]);

        if (null === $rule) {
            throw new \InvalidArgumentException(sprintf('The rule %s does not exist.', $code));
        }

        $output->writeln(sprintf('Information for rule <info>%s</info>', $code));
        $output->writeln('');
        $output->writeln(sprintf('<comment>Code</comment>       %s', $rule->getCode()));
        $output->writeln(sprintf('<comment>Type</comment>       %s', $rule->getType()));
        $output->writeln(sprintf('<comment>Priority</comment>   %s', $rule->getPriority()));
        $output->writeln(sprintf('<comment>Content</comment>    %s', json_encode($rule->getContent())));
    }

    /**
     * @param OutputInterface $output
     */
    protected function listRules(OutputInterface $output)
    {
        $output->writeln('Existing rules...');

        $headers = ['code', 'type', 'priority'];
        $rows = [];
        $rules = $this->ruleDefinitionRepository->findAll();

        foreach ($rules as $rule) {
            $rows[] = [$rule->getCode(), $rule->getType(), $rule->getPriority()];
        }

        $table = new Table($output);
        $table->setHeaders($headers)->setRows($rows);
        $table->render();
    }
}
