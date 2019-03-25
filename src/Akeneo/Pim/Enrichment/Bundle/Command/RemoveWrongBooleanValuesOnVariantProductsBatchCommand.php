<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to fix PIM-7263.
 *
 * This command is an auxiliary command for RemoveWrongBooleanValuesOnVariantProductsCommand
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RemoveWrongBooleanValuesOnVariantProductsBatchCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('pim:catalog:remove-wrong-boolean-values-on-variant-products-batch')
            ->setHidden(true)
            ->addArgument(
                'identifiers',
                InputArgument::REQUIRED,
                'The variant product identifiers to clean (comma separated values)'
            )
            ->setDescription('Auxiliary command for pim:catalog:remove-wrong-boolean-values-on-variant-products-batch')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $cleaner = $this->getContainer()
            ->get('pim_catalog.command.cleaner.wrong_value_on_variant_product');
        $validator = $this->getContainer()->get('pim_catalog.validator.product');
        $identifiers = $input->getArgument('identifiers');
        $variantProducts = $this->getVariantProducts(explode(',', $identifiers));

        $productsToSave = [];
        foreach ($variantProducts as $variantProduct) {
            if (!$variantProduct->isVariant()) {
                continue;
            }

            $isModified = $cleaner->cleanProduct($variantProduct);

            if ($isModified) {
                $violations = $validator->validate($variantProduct);

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
        }

        if (!empty($productsToSave)) {
            $this->getContainer()->get('pim_catalog.saver.product')->saveAll($productsToSave);
        }
    }

    /**
     * @return CursorInterface
     */
    private function getVariantProducts(array $identifiers): CursorInterface
    {
        $pqb = $this->getContainer()
            ->get('pim_catalog.query.product_and_product_model_query_builder_factory')
            ->create();

        $pqb->addFilter('identifier', Operators::IN_LIST, $identifiers);

        return $pqb->execute();
    }
}
