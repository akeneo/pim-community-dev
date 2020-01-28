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

use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinition;
use Akeneo\Tool\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\BulkRemoverInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Command to delete rules
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class DeleteCommand extends Command
{
    protected static $defaultName = 'akeneo:rule:delete';

    /** @var RuleDefinitionRepositoryInterface */
    private $ruleDefinitionRepository;

    /** @var BulkRemoverInterface */
    private $bulkRemover;

    public function __construct(
        RuleDefinitionRepositoryInterface $ruleDefinitionRepository,
        BulkRemoverInterface $bulkRemover
    ) {
        parent::__construct();

        $this->ruleDefinitionRepository = $ruleDefinitionRepository;
        $this->bulkRemover = $bulkRemover;
    }

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
            $this->bulkRemover->removeAll($rules);
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
        if (null !== $code) {
            $rule = $this->ruleDefinitionRepository->findOneBy(['code' => $code]);

            if (null === $rule) {
                throw new \InvalidArgumentException(sprintf('The rule %s does not exist.', $code));
            }

            return [$rule];
        }

        return $this->ruleDefinitionRepository->findAll();
    }
}
