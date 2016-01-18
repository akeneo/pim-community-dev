<?php

namespace Pim\Bundle\CatalogBundle\Command;

use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductTemplateInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to copy values of a variant group to products
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CopyVariantGroupValuesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:product:copy-variant-group-values')
            ->setDescription('Copy variant group values in belonging products')
            ->addArgument(
                'code',
                InputArgument::REQUIRED,
                'Variant group code'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $code = $input->getArgument('code');
        $variantGroup = $this->getVariantGroup($code);
        if (!$variantGroup || $variantGroup->getType()->isVariant() === false) {
            $output->writeln(sprintf('<error>The variant group "%s" does not exist.</error>', $code));

            return -1;
        }

        $template = $variantGroup->getProductTemplate();
        $products = $variantGroup->getProducts();
        $skipped = $this->apply($template, $products->toArray());
        $nbSkipped = count($skipped);

        $output->writeln(
            sprintf(
                '<info>Following %s products have been updated</info>',
                count($products) - $nbSkipped
            )
        );

        foreach ($products as $product) {
            $productIdentifier = (string) $product->getIdentifier();
            if (in_array($productIdentifier, array_keys($skipped)) === false) {
                $output->writeln(sprintf('<info> - "%s" has been updated</info>', $productIdentifier));
            }
        }

        if ($nbSkipped > 0) {
            $output->writeln(
                sprintf(
                    '<info>Following %s products have been skipped</info>',
                    $nbSkipped
                )
            );
        }

        foreach ($skipped as $productIdentifier => $messages) {
            $output->writeln(sprintf('<error> - "%s" is not valid</error>', $productIdentifier));
            foreach ($messages as $message) {
                $output->writeln(sprintf("<error>   - %s</error>", $message));
            }
        }
    }

    /**
     * @param string $code
     *
     * @return Group
     */
    protected function getVariantGroup($code)
    {
        $repository = $this->getContainer()->get('pim_catalog.repository.group');
        $group      = $repository->findOneByCode($code);

        return $group;
    }

    /**
     * @param ProductTemplateInterface $template
     * @param ProductInterface[]       $products
     *
     * @return array $violations
     */
    protected function apply(ProductTemplateInterface $template, $products)
    {
        $applier = $this->getContainer()->get('pim_catalog.applier.product_template');

        return $applier->apply($template, $products);
    }
}
