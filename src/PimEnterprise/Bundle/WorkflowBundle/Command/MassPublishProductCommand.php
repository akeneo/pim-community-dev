<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Command;

use Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager;
use PimEnterprise\Bundle\WorkflowBundle\Publisher\PublisherInterface;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedProductRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Mass publish products
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class MassPublishProductCommand extends ContainerAwareCommand
{
    const BATCH_SIZE = 10;

    const CMD_MAX_SIZE = 1000;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:product:mass-publish')
            ->setDescription('Publish several products')
            ->setHelp('Usage: php app/console pim:product:mass-publish "[\"13353434\"]"')
            ->addArgument(
                'identifiers',
                InputArgument::REQUIRED,
                'Products identifier (sku by default) in json format'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $identifiers = $input->getArgument('identifiers');

        $publishedProductManager = $this->getContainer()->get('pimee_workflow.manager.published_product');
        $productRepository       = $this->getContainer()->get('pim_catalog.repository.product');
        $publishedRepository     = $this->getContainer()->get('pimee_workflow.repository.published_product');
        $objectManager           = $this->getContainer()->get('pim_catalog.object_manager.product');
        $publisher               = $this->getContainer()->get('pimee_workflow.publisher.chained');

        $fullProductsId = json_decode($identifiers, true);
        $productsCount  = count($fullProductsId);
        if (self::CMD_MAX_SIZE < $productsCount) {
            $output->writeln(sprintf(
                '<error>Too much products given. Maximum: %s (%s given).<error>',
                self::CMD_MAX_SIZE,
                $productsCount
            ));

            return 1;
        }

        $chunkedProductsId = array_chunk($fullProductsId, self::BATCH_SIZE);

        $output->writeln('<comment>Publishing products ...<comment>');

        foreach ($chunkedProductsId as $productsId) {
            $productsToPublish = $this->getProducts($productRepository, $productsId, $output);
            $this->publishProducts($publishedProductManager, $productsToPublish, $output);

            $objectManager->flush();
            $objectManager->clear();
        }

        $output->writeln('<comment>Products have been published.<comment>');

        $output->writeln('<comment>Publishing associations ...<comment>');

        foreach ($chunkedProductsId as $productsId) {
            $products = $this->getProducts($productRepository, $productsId, $output);
            $this->publishAssociations($publishedRepository, $publisher, $products, $output);

            $objectManager->flush();
            $objectManager->clear();
        }

        $output->writeln('<comment>Associations have been published.<comment>');

        return 0;
    }

    /**
     * Publish given associations
     *
     * @param PublishedProductRepositoryInterface $publishedRepository
     * @param PublisherInterface                  $publisher
     * @param []ProductInterface                  $products
     * @param OutputInterface                     $output
     */
    protected function publishAssociations(
        PublishedProductRepositoryInterface $publishedRepository,
        PublisherInterface $publisher,
        array $products,
        OutputInterface $output
    ) {
        foreach ($products as $product) {
            $published = $publishedRepository->findOneByOriginalProduct($product);
            foreach ($product->getAssociations() as $association) {
                $copiedAssociation = $publisher->publish($association, ['published' => $published]);
                $published->addAssociation($copiedAssociation);

                $output->writeln(
                    sprintf(
                        '<info>Association of the product "%s" with original id "%s" has been published<info>',
                        $product->getIdentifier(),
                        $product->getId()
                    )
                );
            }
        }
    }

    /**
     * Returns an array of ProductInterface in terms of given identifiers. If a product isn't found, outputs an error.
     *
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param array                                 $productsId
     * @param OutputInterface                       $output
     *
     * @return []ProductInterface
     */
    protected function getProducts(
        IdentifiableObjectRepositoryInterface $repository,
        array $productsId,
        OutputInterface $output
    ) {
        $products = [];
        foreach ($productsId as $productId) {
            $product = $repository->findOneByIdentifier($productId);
            if (!$product) {
                $output->writeln(sprintf('<error>No product found for identifiers "%s"<error>', $productId));
            } else {
                $products[] = $product;
            }
        }

        return $products;
    }

    /**
     * Publish given products
     *
     * @param PublishedProductManager $publishedProductManager
     * @param []ProductInterface      $products
     * @param OutputInterface         $output
     */
    protected function publishProducts(
        PublishedProductManager $publishedProductManager,
        array $products,
        OutputInterface $output
    ) {
        foreach ($products as $product) {
            $publishedProductManager->publish(
                $product,
                ['with_associations' => false, 'flush' => false]
            );

            $output->writeln(
                sprintf(
                    '<info>Product "%s" with original id "%s" has been published<info>',
                    $product->getIdentifier(),
                    $product->getId()
                )
            );
        }
    }
}
