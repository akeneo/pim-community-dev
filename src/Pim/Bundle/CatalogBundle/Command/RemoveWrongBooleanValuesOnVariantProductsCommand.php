<?php
declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Command;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to fix PIM-7263.
 *
 * If some variant products have a some boolean values at their variation level that should belong to their
 * parents instead, this command will remove these values.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RemoveWrongBooleanValuesOnVariantProductsCommand extends ContainerAwareCommand
{
    private const DEFAULT_PRODUCT_BULK_SIZE = 100;

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setHidden(true)
            ->setName('pim:catalog:remove-wrong-boolean-values-on-variant-products')
            ->setDescription('Remove boolean values on variant products that should belong to their parents')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln("<info>Cleaning wrong boolean values on variant products...</info>");

        $productBatchSize = $this->getContainer()->hasParameter('pim_job_product_batch_size') ?
            $this->getContainer()->getParameter('pim_job_product_batch_size') :
            self::DEFAULT_PRODUCT_BULK_SIZE;

        $progressBar = new ProgressBar($output);
        $progressBar->start();

        $variantProducts = $this->getVariantProducts();

        $productsToSave = [];

        foreach ($variantProducts as $variantProduct) {
            $progressBar->advance();

            // TODO: Replace this check for 2.2 version by ``if (!$variantProduct->isVariant()) {}``
            if (!($variantProduct instanceof VariantProductInterface)) {
                continue;
            }

            $productsToSave = $this->getContainer()
                ->get('pim_catalog.updater.remover.wrong_boolean_value_on_variant_products')
                ->removeWrongBooleanValues($variantProduct, $productBatchSize);
        }
        $progressBar->finish();

        if (!empty($productsToSave)) {
            $this->getContainer()->get('pim_catalog.saver.product')->saveAll($productsToSave);
            $this->getContainer()->get('pim_catalog.elasticsearch.indexer.product')->indexAll($productsToSave);
        }

        $output->writeln('');
        $output->writeln(sprintf('<info>%s variant products cleaned</info>', count($productsToSave)));
    }

    /**
     * @return CursorInterface
     */
    private function getVariantProducts(): CursorInterface
    {
        $pqb = $this->getContainer()
            ->get('pim_enrich.query.product_and_product_model_query_builder_factory')
            ->create();

        $pqb->addFilter('parent', Operators::IS_NOT_EMPTY, null);

        return $pqb->execute();
    }
}
