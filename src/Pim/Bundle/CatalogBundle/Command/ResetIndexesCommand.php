<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Command;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Resets the indexes registered in the PIM.
 *
 * @author    Samir Boulil <samir.boulil@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ResetIndexesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:indexes:reset')
            ->addOption(
                'reset-indexes',
                true,
                InputOption::VALUE_NONE,
                'Resets all registered ES indexes prior to reindex'
            )
            ->setDescription('Resets all registered ES indexes');
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->userConfirmation($input, $output)) {
            return;
        }

        $esClients = $this->getEsClients();
        $this->resetIndexes($output, $esClients);

        if (!$this->areIndexesExisting($output, $esClients)) {
            return;
        }

        $this->showSuccessMessages($output);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return bool
     */
    private function userConfirmation(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>This action will entirely remove the product and product model indexes.</info>');
        $question = new ConfirmationQuestion(
            '<question>Are you sure you want to proceed ?</question> (y/N)',
            false
        );
        $question->setMaxAttempts(2);
        $helper = $this->getHelper('question');

        if (!$helper->ask($input, $output, $question)) {
            $output->writeln('<info>Operation aborted. Nothing has been done.</info>');

            return false;
        }

        return true;
    }

    /**
     * Gets the clients from the registry.
     *
     * @return Client[]
     */
    private function getEsClients(): array
    {
        return $this->getContainer()->get('akeneo_elasticsearch.registry.clients')->getClients();
    }

    /**
     * Checks wether the indexes exists.
     *
     * @param OutputInterface $output
     *
     * @return bool
     */
    private function areIndexesExisting(OutputInterface $output, array $esClients): bool
    {
        $errorMessages = [];
        $errorMessage = '- The index "%s" does not exist in Elasticsearch.';

        foreach ($esClients as $esClient) {
            if (!$esClient->hasIndex()) {
                $errorMessages[] = sprintf(
                    $errorMessage,
                    $esClient->getIndexName()
                );
            }
        }

        if (!empty($errorMessages)) {
            $output->writeln('<error>Something wrong happened to those indexes:</error>');
            $output->writeln(implode('\n', $errorMessages));

            $output->writeln('');
            $output->writeln('<error>Please check that the Elasticsearch server is up and accessible and try running the operation again.<error>');

            return false;
        }

        return true;
    }

    /**
     * Reset all the indexes in the registry.
     *
     * @param Client[] $esClients
     */
    private function resetIndexes(OutputInterface $output, array $esClients): void
    {
        foreach ($esClients as $esClient) {
            $output->writeln(sprintf('<info>Resetting the index: %s</info>', $esClient->getIndexName()));
            $esClient->resetIndex();
        }
    }

    /**
     * @param OutputInterface $output
     */
    protected function showSuccessMessages(OutputInterface $output): void
    {
        $output->writeln('');
        $output->writeln('<info>All the registered indexes have been successfully reset!</info>');
        $output->writeln('');
        $output->writeln(
            sprintf(
                '<info>You can now use the command %s and %s to start re-indexing your product and product models.</info>',
                IndexProductCommand::NAME,
                IndexProductModelCommand::NAME
            )
        );
    }

}
