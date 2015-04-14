<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Command;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Validator\Constraints\Null;

/**
 * Unpublishes a product
 *
 * @author Yann Simon
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class UnpublishProductCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('pim:product:unpublish')
            ->setDescription('Unpublish a product')
            ->addArgument(
                'identifier',
                InputArgument::REQUIRED,
                'The product identifier (sku by default)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $identifier = $input->getArgument('identifier');
        $product    = $this->getProduct($identifier);

        if($product === null)
        {
            $output->writeln(sprintf('<error>product with identifier "%s" not found<error>', $identifier));

            return 1;
        }

        $publishedProductManager = $this->getContainer()->get('pimee_workflow.manager.published_product');

        $unpublishedProduct = $publishedProductManager->unpublish($product);

        $output
            ->writeln(
                sprintf(
                    '<info>product "%s" with original id "%s" has been unpublished<info>',
                    $product->getIdentifier(),
                    $product->getId()
                )
            );
    }

    /**
     * @param string $identifier
     *
     * @return ProductInterface|null
     */
    protected function getProduct($identifier)
    {
        $repository = $this->getContainer()->get('pim_catalog.repository.product');

        return $repository->findOneByIdentifier($identifier);
    }
}
