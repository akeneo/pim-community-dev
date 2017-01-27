<?php

namespace Pim\Bundle\CatalogBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Removes product associations.
 *
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RemoveOutdatedProductsFromAssociationsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:product:remove-from-associations')
            ->setDescription('Removes a bulk of product associations (this does not remove the products themselves)')
            ->addArgument(
                'productIds',
                InputArgument::REQUIRED,
                'The list of product IDs to disassociate (comma separated)'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $assocTypeRepository = $this->getContainer()->get('pim_catalog.repository.association_type');
        $productRepository   = $this->getContainer()->get('pim_catalog.repository.product');

        $productIds     = explode(',', $input->getArgument('productIds'));
        $assocTypeCount = $assocTypeRepository->countAll();

        foreach ($productIds as $productId) {
            $productRepository->removeAssociatedProduct($productId, $assocTypeCount);
        }
    }
}
