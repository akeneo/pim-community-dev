<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Command;

use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Query product proposals
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class QueryProductProposalCommand extends ContainerAwareCommand
{
    /* @var integer */
    const DEFAULT_PAGE_SIZE = 10;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $filtersExample = [
            [
                'field'    => 'sku',
                'operator' => 'STARTS WITH',
                'value'    => 'Ak',
            ],
            [
                'field'    => 'author',
                'operator' => '=',
                'value'    => 'mary',
            ]
        ];

        $this
            ->setName('pim:product-proposal:query')
            ->setDescription('Query product proposals')
            ->addArgument(
                'json_filters',
                InputArgument::REQUIRED,
                sprintf("The product proposal filters in json, for instance, '%s'", json_encode($filtersExample))
            )
            ->addOption(
                'json-output',
                false,
                InputOption::VALUE_NONE,
                'If defined, output the result in json format'
            )
            ->addOption(
                'page-size',
                false,
                InputOption::VALUE_OPTIONAL,
                'If defined, display this page',
                self::DEFAULT_PAGE_SIZE
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filters = json_decode($input->getArgument('json_filters'), true);

        $pageSize = $input->getOption('page-size');
        $productProposalQueryBuilder = $this->getProductProposalQueryBuilder($filters);
        $productProposals = $productProposalQueryBuilder->execute();

        if (!$input->getOption('json-output')) {
            $table = $this->buildTable($productProposals, $pageSize, $output);
            $table->render($output);

            $nbProductProposals = count($productProposals);
            if ($nbProductProposals > $pageSize) {
                $output->writeln(
                    sprintf(
                        '<info>%d first product proposals on %d matching these criteria</info>',
                        $pageSize,
                        $nbProductProposals
                    )
                );
            } else {
                $output->writeln(
                    sprintf(
                        '<info>%d product proposals are matching these criteria</info>',
                        $nbProductProposals
                    )
                );
            }
        } else {
            $result = [];
            foreach ($productProposals as $productProposal) {
                $result[] = $productProposal->getId();
            }

            $output->write(json_encode($result));
        }
    }

    /**
     * @param CursorInterface $productProposals
     * @param int             $maxRows
     * @param OutputInterface $output
     *
     * @return Table
     */
    protected function buildTable(CursorInterface $productProposals, $maxRows, OutputInterface $output)
    {
        $rows = [];
        $ind = 0;
        foreach ($productProposals as $productProposal) {
            if ($ind++ < $maxRows) {
                $rows[] = [$productProposal->getId()];
            } else {
                $rows[] = ['...'];
                break;
            }
        }
        $headers = ['id'];
        $table = new Table($output);
        $table->setHeaders($headers)->setRows($rows);

        return $table;
    }

    /**
     * @param array $filters
     *
     * @return ProductQueryBuilderInterface
     */
    protected function getProductProposalQueryBuilder(array $filters)
    {
        $factory = $this->getContainer()->get('pimee_workflow.query.product_proposal_query_builder_factory');

        return $factory->create(['filters' => $filters]);
    }
}
