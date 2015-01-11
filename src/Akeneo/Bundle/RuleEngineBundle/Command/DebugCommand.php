<?php

namespace Akeneo\Bundle\RuleEngineBundle\Command;

use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to display rules information
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class DebugCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:rule:debug')
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
        /** @var RuleDefinitionInterface $rule */
        $rule = $this->getRuleDefinitionRepository()->findOneBy(['code' => $code]);

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
        $rules = $this->getRuleDefinitionRepository()->findAll();

        /** @var RuleDefinitionInterface $rule */
        foreach ($rules as $rule) {
            $rows[] = [$rule->getCode(), $rule->getType(), $rule->getPriority()];
        }

        /** @var TableHelper $table */
        $table = $this->getHelperSet()->get('table');
        $table->setHeaders($headers)->setRows($rows);
        $table->render($output);
    }

    /**
     * @return RuleDefinitionRepositoryInterface
     */
    protected function getRuleDefinitionRepository()
    {
        return $this->getContainer()->get('akeneo_rule_engine.repository.rule_definition');
    }
}
