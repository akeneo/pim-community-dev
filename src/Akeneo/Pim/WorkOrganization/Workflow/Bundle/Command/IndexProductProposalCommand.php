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
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\CountProductModelProposals as CountProductModelProposalsQuery;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\CountProductProposals as CountProductProposalsQuery;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Refresh;
use Akeneo\Tool\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Index product and product model proposals into Elasticsearch
 */
class IndexProductProposalCommand extends Command
{
    protected static $defaultName = 'pimee:product-proposal:index';

    /** @var integer */
    const DEFAULT_BATCH_SIZE = 100;

    /** @var ObjectRepository */
    private $productDraftRepository;

    /** @var BulkIndexerInterface */
    private $productProposalIndexer;

    /** @var CountProductProposalsQuery */
    private $countProductProposals;

    /** @var ObjectRepository */
    private $productModelDraftRepository;

    /** @var BulkIndexerInterface */
    private $productModelDraftIndexer;

    /** @var CountProductModelProposalsQuery */
    private $countProductModelProposals;

    public function __construct(
        ObjectRepository $productDraftRepository,
        BulkIndexerInterface $productProposalIndexer,
        CountProductProposalsQuery $countProductProposals,
        ObjectRepository $productModelDraftRepository,
        BulkIndexerInterface $productModelDraftIndexer,
        CountProductModelProposalsQuery $countProductModelProposals
    ) {
        parent::__construct();

        $this->productDraftRepository = $productDraftRepository;
        $this->productProposalIndexer = $productProposalIndexer;
        $this->countProductProposals = $countProductProposals;
        $this->productModelDraftRepository = $productModelDraftRepository;
        $this->productModelDraftIndexer = $productModelDraftIndexer;
        $this->countProductModelProposals = $countProductModelProposals;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
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
        $proposalsCount = $this->countProductProposals->fetch();

        $output->writeln(sprintf('<info>%s product proposals to index</info>', $proposalsCount));

        $progressBar = new ProgressBar($output, $proposalsCount);
        $progressBar->start();

        $proposalCriteria = ['status' => EntityWithValuesDraftInterface::READY];
        $offset = 0;

        while (!empty($productProposals = $this->productDraftRepository->findBy($proposalCriteria, null, $batchSize, $offset))) {
            $this->productProposalIndexer->indexAll($productProposals, ['index_refresh' => Refresh::disable()]);

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
        $proposalsCount = $this->countProductModelProposals->fetch();

        $output->writeln(sprintf('<info>%s product model proposals to index</info>', $proposalsCount));

        $progressBar = new ProgressBar($output, $proposalsCount);
        $progressBar->start();

        $proposalCriteria = ['status' => EntityWithValuesDraftInterface::READY];
        $offset = 0;

        while (!empty($productModelProposals = $this->productModelDraftRepository->findBy($proposalCriteria, null, $batchSize, $offset))) {
            $this->productModelDraftIndexer->indexAll($productModelProposals, ['index_refresh' => Refresh::disable()]);

            $offset += $batchSize;
            $progressBar->advance(count($productModelProposals));
        }

        $output->writeln('');
    }
}
