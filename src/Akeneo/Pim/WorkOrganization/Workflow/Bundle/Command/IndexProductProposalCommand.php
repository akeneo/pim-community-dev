<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Command;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Index product and product model proposals into Elasticsearch
 */
class IndexProductProposalCommand extends ContainerAwareCommand
{
    public const NAME = 'pimee:product-proposal:index';

    /** @var integer */
    const DEFAULT_BATCH_SIZE = 100;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->addOption(
                'batch-size',
                false,
                InputOption::VALUE_OPTIONAL,
                'Number of proposals to index per batch',
                self::DEFAULT_BATCH_SIZE
            )
            ->setDescription('Index all product and product model proposals into Elasticsearch');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $batchSize = (int) $input->getOption('batch-size') ?? self::DEFAULT_BATCH_SIZE;

        $this->indexProductProposals($batchSize, $output);
        $this->indexProductModelProposals($batchSize, $output);
    }

    /**
     * @param int             $batchSize
     * @param OutputInterface $output
     */
    private function indexProductProposals(int $batchSize, OutputInterface $output): void
    {
        $productDraftRepository = $this->getContainer()->get('pimee_workflow.repository.product_draft');
        $productProposalIndexer = $this->getContainer()->get('pim_catalog.elasticsearch.product_proposal_indexer');

        $proposalsCount = $this->getContainer()->get('pimee_workflow.query.count_product_proposals')->fetch();

        $output->writeln(sprintf('<info>%s product proposals to index</info>', $proposalsCount));

        $progressBar = new ProgressBar($output, $proposalsCount);
        $progressBar->start();

        $proposalCriteria = ['status' => EntityWithValuesDraftInterface::READY];
        $offset = 0;

        while (!empty($productProposals = $productDraftRepository->findBy($proposalCriteria, null, $batchSize, $offset))) {
            $productProposalIndexer->indexAll($productProposals, ['index_refresh' => Refresh::disable()]);

            $offset += $batchSize;
            $progressBar->advance(count($productProposals));
        }

        $output->writeln('');
    }

    /**
     * @param int             $batchSize
     * @param OutputInterface $output
     */
    private function indexProductModelProposals(int $batchSize, OutputInterface $output): void
    {
        $productModelDraftRepository = $this->getContainer()->get('pimee_workflow.repository.product_model_draft');
        $productModelProposalIndexer = $this->getContainer()->get('pim_catalog.elasticsearch.product_model_proposal_indexer');

        $proposalsCount = $this->getContainer()->get('pimee_workflow.query.count_product_model_proposals')->fetch();

        $output->writeln(sprintf('<info>%s product model proposals to index</info>', $proposalsCount));

        $progressBar = new ProgressBar($output, $proposalsCount);
        $progressBar->start();

        $proposalCriteria = ['status' => EntityWithValuesDraftInterface::READY];
        $offset = 0;

        while (!empty($productProposals = $productModelDraftRepository->findBy($proposalCriteria, null, $batchSize, $offset))) {
            $productModelProposalIndexer->indexAll($productProposals, ['index_refresh' => Refresh::disable()]);

            $offset += $batchSize;
            $progressBar->advance(count($productProposals));
        }

        $output->writeln('');
    }
}
