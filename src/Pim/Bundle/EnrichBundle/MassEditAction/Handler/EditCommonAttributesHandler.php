<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Handler;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Akeneo\Component\StorageUtils\Cursor\PaginatorInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactoryInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductMassActionRepositoryInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditCommonAttributesHandler extends AbstractConfigurableStepElement implements StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var ProductMassActionRepositoryInterface */
    protected $massActionRepository;

    /** @var ProductQueryBuilderFactoryInterface */
    protected $pqbFactory;

    /** @var PaginatorFactoryInterface */
    protected $paginatorFactory;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var ProductUpdaterInterface */
    protected $productUpdater;

    /** @var BulkSaverInterface */
    protected $productSaver;

    /** @var ObjectDetacherInterface */
    protected $objectDetacher;

    /** @var array */
    protected $skippedAttributes = [];

    /**
     * @param ProductQueryBuilderFactoryInterface  $pqbFactory
     * @param ProductUpdaterInterface              $productUpdater
     * @param BulkSaverInterface                   $productSaver
     * @param ObjectDetacherInterface              $objectDetacher
     * @param PaginatorFactoryInterface            $paginatorFactory
     * @param ValidatorInterface                   $validator
     * @param ProductMassActionRepositoryInterface $massActionRepository
     * @param AttributeRepositoryInterface         $attributeRepository
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ProductUpdaterInterface $productUpdater,
        BulkSaverInterface $productSaver,
        ObjectDetacherInterface $objectDetacher,
        PaginatorFactoryInterface $paginatorFactory,
        ValidatorInterface $validator,
        ProductMassActionRepositoryInterface $massActionRepository,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->pqbFactory = $pqbFactory;
        $this->paginatorFactory = $paginatorFactory;
        $this->validator = $validator;
        $this->productUpdater = $productUpdater;
        $this->productSaver = $productSaver;
        $this->objectDetacher = $objectDetacher;
        $this->attributeRepository = $attributeRepository;
        $this->massActionRepository = $massActionRepository;
    }

    public function execute(array $configuration)
    {
        $cursor = $this->getProductsCursor($configuration['filters']);
        $paginator = $this->paginatorFactory->createPaginator($cursor);

        $commonAttributeCodes = $this->findCommonAttributeCodes($paginator);
        $commonActions = $this->findCommonActions($configuration['actions'], $commonAttributeCodes);

        if (empty($commonAttributeCodes) || empty($commonActions)) {
            $this->stepExecution->incrementSummaryInfo('skipped_products', $paginator->count());
            $this->stepExecution->addWarning(
                $this->getName(),
                'pim_enrich.mass_edit_action.edit-common-attributes.message.no_valid_attribute',
                [],
                []
            );
        } else {
            $actions = $configuration['actions'];
            foreach ($paginator as $productsPage) {
                $invalidProducts = [];
                foreach ($productsPage as $index => $product) {
                    $this->setData($product, $actions, $commonAttributeCodes);
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

            foreach ($this->skippedAttributes as $skippedAttributeCode) {
                $this->stepExecution->incrementSummaryInfo('skipped_attributes');
                $this->stepExecution->addWarning(
                    $this->getName(),
                    'pim_enrich.mass_edit_action.edit-common-attributes.message.invalid_attribute',
                    ['attribute_code' => $skippedAttributeCode],
                    []
                );
            }
        }

        $values = array_column($configuration['actions'], 'value');
        $this->removeTemporaryFiles($values);
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
     * @param string $code
     */
    protected function addSkippedAttribute($code)
    {
        if (!in_array($code, $this->skippedAttributes)) {
            $this->skippedAttributes[] = $code;
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
     * @param ConstraintViolationListInterface $violations
     * @param ProductInterface                 $product
     */
    protected function addWarningMessage(ConstraintViolationListInterface $violations, ProductInterface $product)
    {
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

    /**
     * Set data from $actions to the given $product
     *
     * @param ProductInterface $product
     * @param array            $actions
     * @param array            $commonAttributeCodes
     *
     * @return UpdateProductHandler
     */
    protected function setData(ProductInterface $product, array $actions, array $commonAttributeCodes)
    {
        foreach ($actions as $action) {
            if (in_array($action['field'], $commonAttributeCodes)) {
                $this->productUpdater->setData($product, $action['field'], $action['value'], $action['options']);
            } else {
                $this->addSkippedAttribute($action['field']);
            }
        }

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
     * Find common attributes for the product retrieved with the given $paginator.
     *
     * @param PaginatorInterface $paginator
     *
     * @return array
     *
     */
    protected function findCommonAttributeCodes(PaginatorInterface $paginator)
    {
        $commonAttributeIds = [];
        $commonAttributeCodes = [];

        foreach ($paginator as $productsPage) {
            $productIds = [];
            foreach ($productsPage as $product) {
                $productIds[] = $product->getId();
            }

            $commonAttributeIds = array_merge(
                $this->massActionRepository->findCommonAttributeIds($productIds),
                $commonAttributeIds
            );
        }

        $commonAttributeIds = array_unique($commonAttributeIds);
        $commonAttributes = $this->attributeRepository->findWithGroups($commonAttributeIds);

        foreach ($commonAttributes as $attribute) {
            $commonAttributeCodes[] = $attribute->getCode();
        }

        return $commonAttributeCodes;
    }

    /**
     * @param array $actions
     * @param array $commonAttributeCodes
     *
     * @return array
     */
    protected function findCommonActions(array $actions, array $commonAttributeCodes)
    {
        if (empty($commonAttributeCodes)) {
            return [];
        }

        $commonActions = [];

        foreach ($actions as $action) {
            if (in_array($action['field'], $commonAttributeCodes)) {
                $commonActions[] = $action;
            }
        }

        return $commonActions;
    }

    /**
     * Remove temporary files used to set product media
     *
     * @param array $values
     */
    protected function removeTemporaryFiles(array $values)
    {
        $filePaths = array_column($values, 'filePath');

        foreach ($filePaths as $filePath) {
            unlink($filePath);
        }
    }
}
