<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Command;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager;
use PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedProductRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Validator\Constraints\Null;

/**
 * Unpublishes a product
 *
 * @author Yann Simon
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

        if (null === $product) {
            $output->writeln(sprintf('<error>product with identifier "%s" not found<error>', $identifier));

            return 1;
        }

        $publishedProduct = $this->getPublishedProduct($product);
        if (null === $publishedProduct) {
            $output->writeln(sprintf('<error>published product with identifier "%s" not found<error>', $identifier));

            return 1;
        }

        $manager = $this->getPublishedProductManager();
        $manager->unpublish($publishedProduct);

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

    /**
     * @param ProductInterface $product
     *
     * @return PublishedProductInterface|null
     */
    protected function getPublishedProduct(ProductInterface $product)
    {
        $repository = $this->getPublishedProductRepository();

        return $repository->findOneByOriginalProduct($product);
    }

    /**
     * @return PublishedProductRepositoryInterface
     */
    protected function getPublishedProductRepository()
    {
        return $this->getContainer()->get('pimee_workflow.repository.published_product');
    }

    /**
     * @return PublishedProductManager
     */
    protected function getPublishedProductManager()
    {
        return $this->getContainer()->get('pimee_workflow.manager.published_product');
    }
}
