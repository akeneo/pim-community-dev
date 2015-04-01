<?php

namespace PimEnterprise\Bundle\EnrichBundle\MassEditAction\Handler;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedProductRepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UnpublishProductHandler extends AbstractConfigurableStepElement
{
    /** @var ProductQueryBuilderFactoryInterface */
    protected $pqbFactory;

    /** @var PublishedProductManager */
    protected $manager;

    /** @var PublishedProductRepositoryInterface */
    protected $repository;

    /** @var PaginatorFactoryInterface */
    protected $paginatorFactory;

    /** @var StepExecution */
    protected $stepExecution;

    /**
     * Constructor.
     *
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param PublishedProductManager             $manager
     * @param PublishedProductRepositoryInterface $repository
     * @param PaginatorFactoryInterface           $paginatorFactory
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        PublishedProductManager $manager,
        PublishedProductRepositoryInterface $repository,
        PaginatorFactoryInterface $paginatorFactory
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->manager = $manager;
        $this->repository = $repository;
        $this->paginatorFactory = $paginatorFactory;
    }

    /**
     * @param array $configuration
     */
    public function execute(array $configuration)
    {
        $filter = current($configuration['filters']);

        if ('id' === $filter['field'] && 'IN' === $filter['operator'] && 1 === count($configuration['filters'])) {
            $publishedProducts = $this->getPublishedProducts($filter);
        } else {
            $publishedProducts = $this->getPublishedProductsThroughOriginals($configuration['filters']);
        }

        foreach ($publishedProducts as $publishedProduct) {
            $this->manager->unpublish($publishedProduct);
            $this->stepExecution->incrementSummaryInfo('mass_unpublished');
            // TODO: validation & skip
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [];
    }

    /**
     * @param StepExecution $stepExecution
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;

        return $this;
    }

    /**
     * Retrieve published products through original products.
     * $filter should be ready to be used with the ProductQueryBuilder.
     *
     * @param array $filters
     *
     * @return array
     */
    protected function getPublishedProductsThroughOriginals(array $filters)
    {
        $cursor = $this->getProductsCursor($filters);
        $paginator = $this->paginatorFactory->createPaginator($cursor);
        $publishedProducts = [];

        foreach ($paginator as $productsPage) {
            foreach ($productsPage as $product) {
                $publishedProducts[] = $this->manager->findPublishedProductByOriginal($product);
            }
        }

        return $publishedProducts;
    }

    /**
     * Retrieve published product through $filter.
     * Warning: Only ids supported !
     *
     * @param array $filter
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    protected function getPublishedProducts(array $filter)
    {
        return $this->repository->findByIds($filter['value']);
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
    protected function getProductsCursor(array $filters)
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
}
