<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command iterate over the given products and product models and save them.
 * Loading a product cleans invalid values (attribute deleted for example) and saving them just after that
 * will update the database, the index and completeness with these new clean values.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RefreshProductCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:product:refresh')
            ->addArgument(
                'identifiers',
                InputArgument::REQUIRED,
                'The product identifiers to clean (comma separated values)'
            )
            ->setHidden(true)
            ->setDescription('Refresh the values of the given products');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $productSaver = $this->getContainer()
            ->get('pim_catalog.saver.product');
        $productModelSaver = $this->getContainer()
            ->get('pim_catalog.saver.product_model');
        $pqbFactory = $this->getContainer()
            ->get('pim_catalog.query.product_and_product_model_query_builder_factory');
        $identifiers = $input->getArgument('identifiers');

        $pqb = $pqbFactory->create();
        $pqb->addFilter('id', Operators::IN_LIST, explode(',', $identifiers));
        $products = $pqb->execute();

        $productsToSave = [
            'product_models' => [],
            'products' => []
        ];
        foreach ($products as $product) {
            $productsToSave[$product instanceof ProductModelInterface ? 'product_models' : 'products'][] = $product;
        }

        $productSaver->saveAll($productsToSave['products']);
        $productModelSaver->saveAll($productsToSave['product_models']);
    }
}
