<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Akeneo\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactoryInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderInterface;
use Pim\Bundle\NotificationBundle\Manager\NotificationManager;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Base class of product mass edit operations
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class ProductMassEditOperation implements MassEditOperationInterface
{
    /** @var BulkSaverInterface */
    protected $productSaver;

    /** @var array */
    protected $pqbFilters = [];

    /** @var ProductQueryBuilderInterface */
    protected $pqbFactory;

    /** @var PaginatorFactoryInterface */
    protected $paginatorFactory;

    /** @var ObjectDetacherInterface */
    protected $objectDetacher;

    /** @var array */
    protected $configuration;

    /**
     * @param BulkSaverInterface                  $productSaver
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param PaginatorFactoryInterface           $paginatorFactory
     * @param ObjectDetacherInterface             $objectDetacher
     */
    public function __construct(
        BulkSaverInterface $productSaver,
        ProductQueryBuilderFactoryInterface $pqbFactory,
        PaginatorFactoryInterface $paginatorFactory,
        ObjectDetacherInterface $objectDetacher
    ) {
        $this->productSaver     = $productSaver;
        $this->pqbFactory       = $pqbFactory;
        $this->paginatorFactory = $paginatorFactory;
        $this->objectDetacher   = $objectDetacher;
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

        foreach ($paginator as $productsPage) {
            foreach ($productsPage as $product) {
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

            $this->productSaver->saveAll($productsPage, $this->getSavingOptions());
            $this->objectDetacher->detach($productsPage);
        }
    }

    /**
     * @return ProductQueryBuilderInterface
     */
    protected function getProductQueryBuilder()
    {
        return $this->pqbFactory->create();
    }

    /**
     * @param array $filters
     *
     * @return \Akeneo\Component\StorageUtils\Cursor\CursorInterface
     */
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
     *
     * TODO: to remove
     */
    public function finalize()
    {
        if (null === $this->productSaver) {
            throw new \LogicException('Product saver must be configured');
        }
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
