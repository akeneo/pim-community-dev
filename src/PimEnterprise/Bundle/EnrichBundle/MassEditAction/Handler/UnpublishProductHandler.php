<?php

namespace PimEnterprise\Bundle\EnrichBundle\MassEditAction\Handler;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactoryInterface;
use PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UnpublishProductHandler extends AbstractConfigurableStepElement implements StepExecutionAwareInterface
{
    /** @var PublishedProductManager */
    protected $manager;

    /** @var PaginatorFactoryInterface */
    protected $paginatorFactory;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var ProductQueryBuilderFactoryInterface */
    protected $publishedPqbFactory;

    /**
     * Constructor.
     *
     * @param ProductQueryBuilderFactoryInterface $publishedPqbFactory
     * @param PublishedProductManager             $manager
     * @param PaginatorFactoryInterface           $paginatorFactory
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $publishedPqbFactory,
        PublishedProductManager $manager,
        PaginatorFactoryInterface $paginatorFactory
    ) {
        $this->manager = $manager;
        $this->paginatorFactory = $paginatorFactory;
        $this->publishedPqbFactory = $publishedPqbFactory;
    }

    /**
     * @param array $configuration
     */
    public function execute(array $configuration)
    {
        $cursor = $this->getPublishedProductsCursor($configuration['filters']);
        $paginator = $this->paginatorFactory->createPaginator($cursor);

        foreach ($paginator as $publishedProductsPage) {
            foreach ($publishedProductsPage as $publishedProduct) {
                $this->manager->unpublish($publishedProduct);
                $this->stepExecution->incrementSummaryInfo('mass_published');
                // TODO: validation & skip
            }
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
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;

        return $this;
    }

    /**
     * @return ProductQueryBuilderInterface
     */
    protected function getPublishedProductQueryBuilder()
    {
        return $this->publishedPqbFactory->create();
    }

    /**
     * @param array $filters
     *
     * @return \Akeneo\Component\StorageUtils\Cursor\CursorInterface
     */
    protected function getPublishedProductsCursor(array $filters)
    {
        $productQueryBuilder = $this->getPublishedProductQueryBuilder();

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
