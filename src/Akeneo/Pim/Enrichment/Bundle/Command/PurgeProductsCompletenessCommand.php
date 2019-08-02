<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessRemoverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command iterate over the given products and purge their completeness
 *
 * @author    Julien Sanchez <jjanvier@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PurgeProductsCompletenessCommand extends Command
{
    protected static $defaultName = 'pim:completeness:purge-products';

    /** @var CompletenessRemoverInterface */
    private $completenessRemover;

    /** @var ProductQueryBuilderFactoryInterface */
    private $queryBuilderFactory;

    public function __construct(
        CompletenessRemoverInterface $completenessRemover,
        ProductQueryBuilderFactoryInterface $queryBuilderFactory
    ) {
        parent::__construct();
        $this->completenessRemover = $completenessRemover;
        $this->queryBuilderFactory = $queryBuilderFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addArgument(
                'identifiers',
                InputArgument::REQUIRED,
                'The product identifiers to purge (comma separated values)'
            )
            ->setHidden(true)
            ->setDescription('Purge the completenesses of the given products');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $identifiers = $input->getArgument('identifiers');

        $pqb = $this->queryBuilderFactory->create();
        $pqb->addFilter('id', Operators::IN_LIST, explode(',', $identifiers));
        $products = $pqb->execute();

        foreach ($products as $product) {
            $this->completenessRemover->removeForProduct($product);
        }
    }
}
