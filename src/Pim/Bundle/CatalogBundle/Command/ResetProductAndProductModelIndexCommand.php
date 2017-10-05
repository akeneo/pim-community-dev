<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Resets the indexes related to the product and product model indexes.
 *
 * @author    Samir Boulil <samir.boulil@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ResetProductAndProductModelIndexCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:product-and-product-model:reset-indexes')
            ->addOption(
                'reset-indexes',
                true,
                InputOption::VALUE_NONE,
                'Resets all the ES indexes prior to reindex'
            )
            ->setDescription('Index all or some products into Elasticsearch');
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

        if (!$this->areIndexesExisting($output)) {
            return;
        }

        $this->resetIndexes();
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
            '<question>Are you sure you want to proceed ?</question> (y/n)',
            false
        );
        $question->setMaxAttempts(2);
        $helper = $this->getHelper('question');

        if (!$helper->ask($input, $output, $question)) {
            return false;
        }

        return true;
    }

    /**
     * @param OutputInterface $output
     *
     * @return bool
     */
    private function areIndexesExisting(OutputInterface $output): bool
    {
        $errorMessages = [];
        $errorMessage = '- The index "%s" does not exist in Elasticsearch.';

        $productClient = $this->getContainer()->get('akeneo_elasticsearch.client.product');
        if (!$productClient->hasIndex()) {
            $errorMessages[] = sprintf(
                $errorMessage,
                $this->getContainer()->getParameter('product_index_name')
            );

        }

        $productAndProductModelClient = $this->getContainer()->get(
            'akeneo_elasticsearch.client.product_and_product_model'
        );
        if (!$productAndProductModelClient->hasIndex()) {
            $errorMessages[] = sprintf(
                $errorMessage,
                $this->getContainer()->getParameter('product_and_product_model_index_name')
            );
        }

        $productModelClient = $this->getContainer()->get('akeneo_elasticsearch.client.product_model');
        if (!$productModelClient->hasIndex()) {
            $errorMessages[] = sprintf(
                $errorMessage,
                $this->getContainer()->getParameter('product_and_product_model_index_name')
            );
        }

        if (!empty($errorMessages)) {
            $output->writeln('<info>Some indexes you want to reset do not exist:');
            $output->writeln(implode('\n', $errorMessages));

            $output->writeln('');
            $output->writeln('<info>Something might be wrong with your installation. Nothing has been done</info>');

            return false;
        }

        return true;
    }

    /**
     * Reset all the indexes related to the products and product models.
     */
    private function resetIndexes(): void
    {
        $productClient = $this->getContainer()->get('akeneo_elasticsearch.client.product');
        $productAndProductModelClient = $this->getContainer()->get(
            'akeneo_elasticsearch.client.product_and_product_model'
        );
        $productModelClient = $this->getContainer()->get('akeneo_elasticsearch.client.product_model');

        $productClient->resetIndex();
        $productModelClient->resetIndex();
        $productAndProductModelClient->resetIndex();
    }

    /**
     * @param OutputInterface $output
     */
    protected function showSuccessMessages(OutputInterface $output): void
    {
        $output->writeln('');
        $output->writeln('<info>The product and product models indexes have been successfully reset!</info>');
        $output->writeln('');
        $output->writeln(
            sprintf(
                '<info>You can now use the command %s and %s to start re-indexing your product and product models.</info>',
                IndexProductCommand::PRODUCT_INDEX_COMMAND,
                IndexProductModelCommand::PRODUCT_MODEL_INDEX_COMMAND
            )
        );
    }

}
