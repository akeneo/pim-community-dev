<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Akeneo\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactoryInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Base class of product mass edit operations
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class ProductMassEditOperation extends AbstractMassEditAction
{
    /** @var BulkSaverInterface */
    protected $productSaver;

    /** @var array */
    protected $pqbFilters = [];

    /** @var ProductQueryBuilderInterface */
    protected $pqbFactory;

    /** @var PaginatorFactoryInterface */
    protected $paginatorFactory;

    /** @var array */
    protected $configuration = [];

    /**
     * @param BulkSaverInterface                  $productSaver
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param PaginatorFactoryInterface           $paginatorFactory
     */
    public function __construct(
        BulkSaverInterface $productSaver,
        ProductQueryBuilderFactoryInterface $pqbFactory,
        PaginatorFactoryInterface $paginatorFactory
    ) {
        $this->productSaver = $productSaver;
        $this->pqbFactory = $pqbFactory;
        $this->paginatorFactory = $paginatorFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function affectsCompleteness()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        $this->readConfiguration();

        $cursor = $this->getProducts($this->pqbFilters);
        $paginator = $this->paginatorFactory->createPaginator($cursor);

        foreach ($paginator as $products) {
            foreach ($products as $product) {
                if (!$product instanceof ProductInterface) {
                    throw new \LogicException(
                        sprintf(
                            'Cannot perform mass edit action "%s" on object of type "%s", '.
                            'expecting "Pim\Bundle\CatalogBundle\Model\ProductInterface"',
                            __CLASS__,
                            get_class($product)
                        )
                    );
                }

                $this->doPerform($product);
            }

            // TODO: Get the fix of @nidup for cache etc. Why not handle this elsewhere (eg. finalize)
            $this->productSaver->saveAll($products, $this->getSavingOptions());
        }
    }

    protected function getProductQueryBuilder()
    {
        return $this->pqbFactory->create();
    }

    protected function getProducts(array $filters)
    {
        $productQueryBuilder = $this->getProductQueryBuilder();

        $resolver = new OptionsResolver();
        $resolver->setRequired(['field', 'operator', 'value']);
        $resolver->setOptional(['locale', 'scope']);
        $resolver->setDefaults(['locale' => null, 'scope' => null]);

        foreach ($filters as $filter) {
            $filter = $resolver->resolve($filter);
            $context = ['locale' => $filter['locale'], 'scope' => $filter['scope']];
            $productQueryBuilder->addFilter($filter['field'], $filter['operator'], $filter['value'], $context);
        }

        return $productQueryBuilder->execute();
    }

    /**
     * Return the options to use when save all products
     *
     * @return array
     */
    public function getSavingOptions()
    {
        return [
            'recalculate' => false,
            'flush'       => true,
            'schedule'    => $this->affectsCompleteness()
        ];
    }

    /**
     * Finalize the operation
     */
    public function finalize()
    {
        if (null === $this->productSaver) {
            throw new \LogicException('Product saver must be configured');
        }
        $products = $this->getObjectsToMassEdit();
        $this->productSaver->saveAll($products, $this->getSavingOptions());
    }

    /**
     * @return array
     */
    public function getPqbFilters()
    {
        return $this->pqbFilters;
    }

    /**
     * @param array $pqbFilters
     *
     * @return $this
     */
    public function setPqbFilters($pqbFilters)
    {
        $this->pqbFilters = $pqbFilters;

        return $this;
    }

    /**
     * @return array
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param array $configuration
     *
     * @return $this
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;

        return $this;
    }

    /**
     * Read the operation configuration for the specific job
     *
     * @return $this
     */
    abstract protected function readConfiguration();

    /**
     * Save the current specific configuration
     *
     * @return $this
     */
    abstract public function saveConfiguration();

    /**
     * Perform operation on the product instance
     *
     * @param ProductInterface $product
     *
     * @return null
     *
     * @throw \RuntimeException if operation cannot be performed on the given product
     */
    abstract protected function doPerform(ProductInterface $product);
}
