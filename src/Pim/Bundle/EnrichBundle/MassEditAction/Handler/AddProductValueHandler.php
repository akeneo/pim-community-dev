<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Handler;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactoryInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddProductValueHandler extends AbstractConfigurableStepElement implements StepExecutionAwareInterface
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

    /** @var ValidatorInterface */
    protected $validator;

    /**
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param ProductUpdaterInterface             $productUpdater
     * @param BulkSaverInterface                  $productSaver
     * @param ObjectDetacherInterface             $objectDetacher
     * @param PaginatorFactoryInterface           $paginatorFactory
     * @param ValidatorInterface                  $validator
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ProductUpdaterInterface $productUpdater,
        BulkSaverInterface $productSaver,
        ObjectDetacherInterface $objectDetacher,
        PaginatorFactoryInterface $paginatorFactory,
        ValidatorInterface $validator
    ) {
        $this->pqbFactory       = $pqbFactory;
        $this->productUpdater   = $productUpdater;
        $this->productSaver     = $productSaver;
        $this->objectDetacher   = $objectDetacher;
        $this->paginatorFactory = $paginatorFactory;
        $this->validator        = $validator;
    }

    /**
     * @param array $configuration
     */
    public function execute(array $configuration)
    {
        $cursor = $this->getProductsCursor($configuration['filters']);
        $paginator = $this->paginatorFactory->createPaginator($cursor);
        $actions = $configuration['actions'];

        foreach ($paginator as $productsPage) {
            $invalidProducts = [];
            foreach ($productsPage as $index => $product) {
                $this->addData($product, $actions);
                $violations = $this->validator->validate($product);

                if (0 < $violations->count()) {
                    $this->addWarningMessage($violations, $product);
                    $this->stepExecution->incrementSummaryInfo('skipped_products');
                    $invalidProducts[$index] = $product;
                } else {
                    $this->stepExecution->incrementSummaryInfo('mass_edited');
                }
            }

            $productsPage = array_diff_key($productsPage, $invalidProducts);
            $this->detachProducts($invalidProducts);
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

    /**
     * Add data from $actions to the given $product
     *
     * @param ProductInterface $product
     * @param array            $actions
     *
     * @return AddProductValueHandler
     */
    protected function addData(ProductInterface $product, array $actions)
    {
        foreach ($actions as $action) {
            $this->productUpdater->addData($product, $action['field'], $action['value']);
        }

        return $this;
    }

    /**
     * @param ConstraintViolationListInterface $violations
     * @param ProductInterface                 $product
     */
    protected function addWarningMessage(
        ConstraintViolationListInterface $violations,
        ProductInterface $product
    ) {
        foreach ($violations as $violation) {
            // TODO re-format the message, property path doesn't exist for class constraint
            // for instance cf VariantGroupAxis
            $invalidValue = $violation->getInvalidValue();
            if (is_object($invalidValue) && method_exists($invalidValue, '__toString')) {
                $invalidValue = (string) $invalidValue;
            } elseif (is_object($invalidValue)) {
                $invalidValue = get_class($invalidValue);
            }
            $errors = sprintf(
                "%s: %s: %s\n",
                $violation->getPropertyPath(),
                $violation->getMessage(),
                $invalidValue
            );
            $this->stepExecution->addWarning($this->getName(), $errors, [], $product);
        }
    }
}
