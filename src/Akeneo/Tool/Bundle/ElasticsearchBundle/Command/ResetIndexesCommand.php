<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ElasticsearchBundle\Command;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
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
            ->setName('akeneo:elasticsearch:reset-indexes')
            ->addOption(
                'reset-indexes',
                true,
                InputOption::VALUE_NONE,
                'Resets registered ES indexes prior to reindex'
            )
            ->addOption('index', 'i', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'ES index name to reset')
            ->setDescription('Resets all registered ES indexes');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->userConfirmation($input, $output)) {
            return;
        }

        $esClients = $this->getFilteredEsClients($input);
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
    private function userConfirmation(InputInterface $input, OutputInterface $output): bool
    {
        $esClients = $this->getFilteredEsClients($input);
        if (empty($esClients)) {
            $output->writeln('<info>There is not any index to reset. Maybe you provided an index to reset that does not exist.</info>');
        }

        $output->writeln('<info>This action will entirely reset the following indexes in the PIM:</info>');
        foreach ($esClients as $esClient) {
            $output->writeln(sprintf('<info>%s</info>', $esClient->getIndexName()));
        }

        $question = new ConfirmationQuestion(
            '<question>Are you sure you want to proceed ?</question> (Y/n)',
            true
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
     * Gets the clients to reset filtered by those provided in the arguments of the function.
     *
     * @return Client[]
     */
    private function getFilteredEsClients(InputInterface $input): array
    {
        $esClients = $this->getEsClients();
        $selectedIndexes = $input->getOption('index');

        if (empty($selectedIndexes)) {
            return $esClients;
        }

        $filteredEsClients = array_filter($esClients, function (Client $client) use ($selectedIndexes) {
            return in_array($client->getIndexName(), $selectedIndexes);
        });

        return $filteredEsClients;
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
     * @param Client[]        $esClients
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
        $output->writeln('<info>All the indexes have been successfully reset!</info>');
        $output->writeln('');
        $output->writeln('<info>You can now use the following commands to manually re-index the entities you want:</info>');
        $commands = $this->getContainer()->getParameter('elasticsearch_indexing_commands');
        foreach ($commands as $command) {
            $output->writeln(sprintf('    <info>- %s</info>', $command::NAME));
        }
    }
}
