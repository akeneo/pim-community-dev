<?php

declare(strict_types = 1);

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Job\ComputeFamilyVariantStructureChangesTasklet;
use Pim\Component\Catalog\EntityWithFamilyVariant\KeepOnlyValuesForVariation;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Repository\ProductModelRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to compute products and products models changes on family variant update
 *
 * @author  Simon CARRE <simon.carre@clickandmortar.fr>
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ComputeFamilyVariantStructureChangesCommand extends ContainerAwareCommand
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ProductModelRepositoryInterface
     */
    protected $productModelRepository;

    /**
     * @var BulkSaverInterface
     */
    protected $productSaver;

    /**
     * @var BulkSaverInterface
     */
    protected $productModelSaver;

    /**
     * @var KeepOnlyValuesForVariation
     */
    protected $keepOnlyValuesForVariation;

    /**
     * Configure command
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('pim:catalog:compute-family-variant-changes')
             ->setDescription('Command to compute products changes on family variant update')
             ->addArgument('productsIds', InputArgument::REQUIRED, 'Products ids to update')
             ->addOption('type', 't', InputOption::VALUE_REQUIRED, 'Type of ids: Product or ProductModel', ComputeFamilyVariantStructureChangesTasklet::TYPE_PRODUCT);
    }

    /**
     * Execute command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type                = $input->getOption('type');
        $productsIdsAsString = $input->getArgument('productsIds');
        $productsIds         = explode(ComputeFamilyVariantStructureChangesTasklet::COMMAND_SEPARATOR, $productsIdsAsString);
        $this->loadServices();
        if ($type === ComputeFamilyVariantStructureChangesTasklet::TYPE_PRODUCT_MODEL) {
            $productsModels = $this->productModelRepository->findById($productsIds);
            $this->computeProductsModels($productsModels);
        } else {
            $products = $this->productRepository->findById($productsIds);
            $this->computeProducts($products);
        }

        return;
    }

    /**
     * Load services from container
     *
     * @return void
     */
    protected function loadServices()
    {
        $container                        = $this->getContainer();
        $this->productRepository          = $container->get('pim_catalog.repository.product');
        $this->productModelRepository     = $container->get('pim_catalog.repository.product_model');
        $this->productSaver               = $container->get('pim_catalog.saver.product');
        $this->productModelSaver          = $container->get('pim_catalog.saver.product_model');
        $this->keepOnlyValuesForVariation = $container->get('pim_catalog.entity_with_family_variant.keep_only_values_for_variation');
    }

    /**
     * Compute products changes
     *
     * @param ProductInterface[] $products
     *
     * @return void
     */
    protected function computeProducts($products)
    {
        $this->keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant($products);
        $this->productSaver->saveAll($products);
    }

    /**
     * Compute products models changes
     *
     * @param ProductModelInterface[] $productsModels
     *
     * @return void
     */
    protected function computeProductsModels($productsModels)
    {
        $this->keepOnlyValuesForVariation->updateEntitiesWithFamilyVariant($productsModels);
        $this->productModelSaver->saveAll($productsModels);
    }
}
