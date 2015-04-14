<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Command;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Publishes a product
 *
 * @author    Nina Sarradin
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PublishProductCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:product:publish')
            ->setDescription('Publish a product')
            ->addArgument(
                'identifier',
                InputArgument::REQUIRED,
                'The product identifier (sku by default)'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $identifier = $input->getArgument('identifier');
        $product    = $this->getProduct($identifier);

        if (null === $product) {
            $output->writeln(sprintf('<error>product with identifier "%s" not found<error>', $identifier));

            return 1;
        }

        $publishedProductManager = $this->getContainer()->get('pimee_workflow.manager.published_product');
        $publishedProduct = $publishedProductManager->publish($product);

        $output
            ->writeln(
                sprintf(
                    '<info>product "%s" with original id "%s" has been published with the new id "%s"<info>',
                    $product->getIdentifier(),
                    $product->getId(),
                    $publishedProduct->getId()
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
