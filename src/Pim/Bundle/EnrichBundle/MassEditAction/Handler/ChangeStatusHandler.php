<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Handler;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactoryInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
* @author    Adrien Pétremann <adrien.petremann@akeneo.com>
* @copyright 2015 Akeneo SAS (http://www.akeneo.com)
* @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/
class ChangeStatusHandler extends AbstractConfigurableStepElement implements StepExecutionAwareInterface
{
    /** @var ProductQueryBuilderFactoryInterface */
    protected $pqbFactory;

    /** @var ProductUpdaterInterface */
    protected $productUpdater;

    /** @var BulkSaverInterface */
    protected $productSaver;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var ObjectDetacherInterface */
    protected $objectDetacher;

    /** @var PaginatorFactoryInterface */
    protected $paginatorFactory;

    /**
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param ProductUpdaterInterface             $productUpdater
     * @param BulkSaverInterface                  $productSaver
     * @param ObjectDetacherInterface             $objectDetacher
     * @param PaginatorFactoryInterface           $paginatorFactory
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ProductUpdaterInterface $productUpdater,
        BulkSaverInterface $productSaver,
        ObjectDetacherInterface $objectDetacher,
        PaginatorFactoryInterface $paginatorFactory
    ) {
        $this->pqbFactory       = $pqbFactory;
        $this->productUpdater   = $productUpdater;
        $this->productSaver     = $productSaver;
        $this->objectDetacher   = $objectDetacher;
        $this->paginatorFactory = $paginatorFactory;
    }

    /**
     * @param array $configuration
     */
    public function execute(array $configuration)
    {
        $cursor = $this->getProducts($configuration['filters']);
        $paginator = $this->paginatorFactory->createPaginator($cursor);
        $actions = $configuration['actions'];
        $action = $actions[0];

        foreach ($paginator as $productsPage) {
            foreach ($productsPage as $product) {
                // TODO: Use this with the new version of productUpdater :
//                foreach ($actions as $action) {
//                    $this->productUpdater->setValue($product, $action['field'], $action['value']);
//                }

                $product->setEnabled($action['value']);
//                $product->setFamilyId($action['value']);
                $this->stepExecution->incrementSummaryInfo('mass_edited');
            }


            $this->productSaver->saveAll($productsPage, $this->getSavingOptions());
            $this->detachProducts($productsPage);
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
     * @param array $productsPage
     */
    protected function detachProducts(array $productsPage)
    {
        foreach ($productsPage as $product) {
            $this->objectDetacher->detach($product);
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
    protected function getSavingOptions()
    {
        return [
            'recalculate' => false,
            'flush'       => true,
            'schedule'    => false
        ];
    }
}
