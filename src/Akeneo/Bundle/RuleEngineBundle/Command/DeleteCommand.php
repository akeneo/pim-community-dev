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

use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinition;
use Akeneo\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Component\StorageUtils\Remover\BulkRemoverInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Command to delete rules
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class DeleteCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('akeneo:rule:delete')
            ->addArgument('code', InputArgument::OPTIONAL, 'Code of the rule to delete')
            ->setDescription('Deletes all the rules or only one if a code is provided.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $code = $input->getArgument('code');

        if ($this->confirmDeletion($input, $output, $code)) {
            $rules = $this->getRules($code);
            $remover = $this->getRuleDefinitionRemover();
            $remover->removeAll($rules);
            $output->writeln('<info>Done !</info>');
        }
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param string          $code
     *
     * @return bool
     */
    protected function confirmDeletion(InputInterface $input, OutputInterface $output, $code)
    {
        $question = null !== $code ?
            sprintf('Are you sure you want to delete the rule "%s"?', $code) :
            'Are you sure you want to delete all the rules?';

        $dialog = $this->getHelper('question');

        return $dialog->ask($input, $output, new ConfirmationQuestion($question, false));
    }

    /**
     * @param string $code
     *
     * @return RuleDefinition[]
     */
    protected function getRules($code)
    {
        $repository = $this->getRuleDefinitionRepository();

        if (null !== $code) {
            $rule = $repository->findOneBy(['code' => $code]);

            if (null === $rule) {
                throw new \InvalidArgumentException(sprintf('The rule %s does not exist.', $code));
            }

            return [$rule];
        }

        return $repository->findAll();
    }

    /**
     * @return RuleDefinitionRepositoryInterface
     */
    protected function getRuleDefinitionRepository()
    {
        return $this->getContainer()->get('akeneo_rule_engine.repository.rule_definition');
    }

    /**
     * @return BulkRemoverInterface
     */
    protected function getRuleDefinitionRemover()
    {
        return $this->getContainer()->get('akeneo_rule_engine.remover.rule_definition');
    }
}
