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
use Akeneo\Component\Persistence\BulkRemoverInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
            ->setName('pim:rule:delete')
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

        if (true === $this->confirmDeletion($output, $code)) {
            $rules = $this->getRules($code);
            $remover = $this->getRuleDefinitionRemover();
            $remover->removeAll($rules);
            $output->writeln('<info>Done !</info>');
        }
    }

    /**
     * @param OutputInterface $output
     * @param string          $code
     *
     * @return bool
     */
    protected function confirmDeletion(OutputInterface $output, $code)
    {
        $question = null !== $code ?
            sprintf('Are you sure you want to delete the rule "%s"?', $code):
            'Are you sure you want to delete all the rules?';

        /** @var DialogHelper $dialog */
        $dialog = $this->getHelper('dialog');

        return $dialog->askConfirmation($output, sprintf('<question>%s</question>', $question), false);
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
