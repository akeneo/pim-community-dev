<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

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
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('pim:catalog:remove-wrong-boolean-values-on-variant-products')
            ->setAliases(['pim:catalog:remove-wrong-values-on-variant-products'])
            ->setDescription('Remove boolean values on variant products that should belong to their parents')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $io = new SymfonyStyle($input, $output);
        $io->text('Cleaning wrong boolean values on variant products...');

        $productBatchSize = $this->getContainer()->getParameter('pim_job_product_batch_size');
        $rootDir = $this->getContainer()->get('kernel')->getRootDir();
        $env = $input->getOption('env');
        $cacheClearer = $this->getContainer()->get('pim_connector.doctrine.cache_clearer');

        $variantProducts = $this->getVariantProducts();

        $io->progressStart($variantProducts->count());

        $productsToClean = [];
        foreach ($variantProducts as $variantProduct) {
            $productsToClean[] = $variantProduct instanceof ProductModelInterface ? $variantProduct->getCode() : $variantProduct->getIdentifier();

            if (count($productsToClean) >= $productBatchSize) {
                $this->launchCleanTask($productsToClean, $env, $rootDir);
                $cacheClearer->clear();
                $io->progressAdvance(count($productsToClean));
                $productsToClean = [];
            }
        }

        if (!empty($productsToClean)) {
            $this->launchCleanTask($productsToClean, $env, $rootDir);
            $io->progressAdvance(count($productsToClean));
        }

        $io->progressFinish();
        $io->text('Cleaning wrong boolean values on variant products [DONE]');
    }

    /**
     * @return CursorInterface
     */
    private function getVariantProducts(): CursorInterface
    {
        $pqb = $this->getContainer()
            ->get('pim_catalog.query.product_and_product_model_query_builder_factory')
            ->create();

        $pqb->addFilter('parent', Operators::IS_NOT_EMPTY, null);

        return $pqb->execute();
    }

    /**
     * Lanches the clean command on given ids
     *
     * @param array  $productIdentifiers
     * @param string $env
     * @param string $rootDir
     */
    private function launchCleanTask(array $productIdentifiers, string $env, string $rootDir)
    {
        $process = new Process([
            sprintf('%s/../bin/console', $rootDir),
            'pim:catalog:remove-wrong-boolean-values-on-variant-products-batch',
            sprintf('--env=%s', $env),
            implode(',', $productIdentifiers)
        ]);
        $process->run();
    }
}
