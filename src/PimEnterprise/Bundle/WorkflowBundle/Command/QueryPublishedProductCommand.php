<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Command;

use Pim\Bundle\CatalogBundle\Command\QueryProductCommand;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Query published products
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class QueryPublishedProductCommand extends QueryProductCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $filtersExample = [
            [
                'field'    => 'sku',
                'operator' => 'STARTS WITH',
                'value'    => 'Ak'
            ],
            [
                'field'    => 'completeness',
                'operator' => '=',
                'value'    => '100',
                'locale'   => 'en_US',
                'scope'    => 'print'
            ]
        ];

        $this
            ->setName('pim:published-product:query')
            ->setDescription('Query published products')
            ->addArgument(
                'json_filters',
                InputArgument::REQUIRED,
                sprintf("The published product filters in json, for instance, '%s'", json_encode($filtersExample))
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
     * @return ProductQueryBuilderInterface
     */
    protected function getProductQueryBuilder()
    {
        $factory = $this->getContainer()->get('pimee_workflow.doctrine.query.published_product_query_factory');

        return $factory->create();
    }
}
