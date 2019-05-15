<?php
declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Command;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

/**
 * Command to fix PIM-7263.
 *
 * If some variant products have a some values at their variation level that should belong to their
 * parents instead, this command will remove these values.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RemoveWrongValuesOnVariantProductsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('pim:catalog:remove-wrong-values-on-variant-products')
            ->addArgument(
                'family',
                InputArgument::OPTIONAL,
                'Code of the family to clean'
            )
            ->setDescription('Remove values on variant products that should belong to their parents')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $io = new SymfonyStyle($input, $output);
        $io->text('Cleaning wrong values on variant products...');

        $productBatchSize = $this->getContainer()->getParameter('pim_job_product_batch_size');
        $rootDir = $this->getContainer()->get('kernel')->getRootDir();
        $family = $input->getArgument('family');
        $env = $this->getContainer()->get('kernel')->getEnvironment();
        $cacheClearer = $this->getContainer()->get('pim_connector.doctrine.cache_clearer');

        $variantProducts = $this->getVariantProducts($family);

        $io->progressStart($variantProducts->count());

        $productsToClean = [];
        foreach ($variantProducts as $variantProduct) {
            if ($variantProduct instanceof ProductInterface && !$variantProduct->isVariant()) {
                continue;
            }
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
        $io->text('Cleaning wrong values on variant products [DONE]');
    }

    /**
     * @return CursorInterface
     */
    private function getVariantProducts(?string $familyCode): CursorInterface
    {
        $pqb = $this->getContainer()
            ->get('pim_catalog.query.product_and_product_model_query_builder_factory')
            ->create();

        $pqb->addFilter('parent', Operators::IS_NOT_EMPTY, null);
        if (null !== $familyCode) {
            $pqb->addFilter('family', Operators::IN_LIST, [$familyCode]);
        }

        return $pqb->execute();
    }

    /**
     * Lanches the clean command on given identifiers
     *
     * @param array  $productIdentifiers
     * @param string $env
     * @param string $rootDir
     */
    private function launchCleanTask(array $productIdentifiers, string $env, string $rootDir)
    {
        $process = new Process([
            sprintf('%s/../bin/console', $rootDir),
            'pim:catalog:remove-wrong-values-on-variant-products-batch',
            sprintf('--env=%s', $env),
            implode(',', $productIdentifiers)
        ]);
        $process->run();
    }
}
