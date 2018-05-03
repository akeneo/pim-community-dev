<?php
declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\Command;

use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

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

        $progressBatchSize = 50;
        $productBatchSize = $this->getContainer()->hasParameter('pim_job_product_batch_size') ?
            $this->getContainer()->getParameter('pim_job_product_batch_size') :
            self::DEFAULT_PRODUCT_BULK_SIZE;

        $variantProducts = $this->getVariantProducts();

        $io->progressStart($variantProducts->count());

        $productsToSave = [];
        $productsParsedCount = 0;

        foreach ($variantProducts as $variantProduct) {
            if (!$variantProduct instanceof ProductInterface || !$variantProduct->isVariant()) {
                continue;
            }

            $isModified = $this->getContainer()
                ->get('pim_catalog.command.cleaner.wrong_boolean_value_on_variant_product')
                ->cleanProduct($variantProduct);

            if ($isModified) {
                $violations = $this->getContainer()->get('pim_catalog.validator.product')->validate($variantProduct);

                if ($violations->count() > 0) {
                    throw new \LogicException(
                        sprintf(
                            'Product "%s" is not valid and cannot be saved',
                            $variantProduct->getIdentifier()
                        )
                    );
                }

                $productsToSave[] = $variantProduct;
            }

            if (count($productsToSave) >= $productBatchSize) {
                $this->getContainer()->get('pim_catalog.saver.product')->saveAll($productsToSave);
                $productsToSave = [];
            }

            $productsParsedCount++;
            if ($productsParsedCount >= $progressBatchSize) {
                $io->progressAdvance($productsParsedCount);
                $productsParsedCount = 0;
            }
        }

        if (!empty($productsToSave)) {
            $this->getContainer()->get('pim_catalog.saver.product')->saveAll($productsToSave);
        }

        $io->progressFinish();

        $io->newLine();
        $io->text(sprintf('%s variant products cleaned', count($productsToSave)));
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
}
