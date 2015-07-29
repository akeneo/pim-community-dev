<?php

namespace Pim\Bundle\EnrichBundle\Connector\Item\MassEdit;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Akeneo\Component\StorageUtils\Cursor\PaginatorInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactoryInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Component\Connector\Repository\JobConfigurationRepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * This step checks if there is no duplicate variant axis values between all products
 * in the user selection.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupCleaner extends AbstractConfigurableStepElement implements StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var JobConfigurationRepositoryInterface */
    protected $jobConfigurationRepo;

    /** @var ProductQueryBuilderFactoryInterface */
    protected $pqbFactory;

    /** @var PaginatorFactoryInterface */
    protected $paginatorFactory;

    /** @var ObjectDetacherInterface */
    protected $objectDetacher;

    /**  @var IdentifiableObjectRepositoryInterface */
    protected $groupRepository;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var SaverInterface */
    protected $jobConfigurationSaver;

    /**
     * @param ProductQueryBuilderFactoryInterface   $pqbFactory
     * @param PaginatorFactoryInterface             $paginatorFactory
     * @param ObjectDetacherInterface               $objectDetacher
     * @param JobConfigurationRepositoryInterface   $jobConfigurationRepo
     * @param IdentifiableObjectRepositoryInterface $groupRepository
     * @param ProductRepositoryInterface            $productRepository
     * @param TranslatorInterface                   $translator
     * @param SaverInterface                        $jobConfigurationSaver
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        PaginatorFactoryInterface $paginatorFactory,
        ObjectDetacherInterface $objectDetacher,
        JobConfigurationRepositoryInterface $jobConfigurationRepo,
        IdentifiableObjectRepositoryInterface $groupRepository,
        ProductRepositoryInterface $productRepository,
        TranslatorInterface $translator,
        SaverInterface $jobConfigurationSaver
    ) {
        $this->pqbFactory           = $pqbFactory;
        $this->paginatorFactory     = $paginatorFactory;
        $this->objectDetacher       = $objectDetacher;
        $this->jobConfigurationRepo = $jobConfigurationRepo;
        $this->groupRepository      = $groupRepository;
        $this->productRepository    = $productRepository;
        $this->translator           = $translator;
        $this->jobConfigurationSaver = $jobConfigurationSaver;
    }

    /**
     * @param array $configuration
     */
    public function execute(array $configuration)
    {
        $actions = $configuration['actions'];

        $variantGroupCode = $actions['value'];

        $variantGroup = $this->groupRepository->findOneByIdentifier($variantGroupCode);

        $axisAttributeCodes = $this->getAxisAttributeCodes($variantGroup);
        $eligibleProductIds = $this->productRepository->getEligibleProductIdsForVariantGroup($variantGroup->getId());

        $cursor = $this->getProductsCursor($configuration['filters']);
        $paginator = $this->paginatorFactory->createPaginator($cursor);

        list($productAttributeAxis, $acceptedIds) = $this->filterDuplicateAxisCombinations(
            $paginator,
            $eligibleProductIds,
            $axisAttributeCodes
        );

        $excludedIds = $this->addSkippedMessageForDuplicatedProducts($productAttributeAxis);
        $acceptedIds = array_diff($acceptedIds, $excludedIds);

        $configuration['filters'] = [['field' => 'id', 'operator' => 'IN', 'value' => $acceptedIds]];

        if (0 === count($acceptedIds)) {
            $configuration = null;
        }

        $this->setJobConfiguration(json_encode($configuration));
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [];
    }

    /**
     * Save the job configuration
     *
     * @param string $configuration
     */
    protected function setJobConfiguration($configuration)
    {
        $jobExecution    = $this->stepExecution->getJobExecution();
        $massEditJobConf = $this->jobConfigurationRepo->findOneBy(['jobExecution' => $jobExecution]);
        $massEditJobConf->setConfiguration($configuration);

        $this->jobConfigurationSaver->save($massEditJobConf);
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
        $resolver->setRequired(['field', 'operator', 'value'])
            ->setDefined(['context'])
            ->setDefaults([
                'context' => ['locale' => null, 'scope' => null]
            ]);

        foreach ($filters as $filter) {
            $filter = $resolver->resolve($filter);
            $productQueryBuilder->addFilter(
                $filter['field'],
                $filter['operator'],
                $filter['value'],
                $filter['context']
            );
        }

        return $productQueryBuilder->execute();
    }

    /**
     * @param ConstraintViolationListInterface $violations
     * @param ProductInterface                 $product
     */
    protected function addWarningMessage($violations, $product)
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
     * @param GroupInterface $variantGroup
     *
     * @return array
     */
    protected function getAxisAttributeCodes($variantGroup)
    {
        $axisAttributes = $variantGroup->getAxisAttributes();

        $axisAttributeCodes = [];
        foreach ($axisAttributes as $axisAttribute) {
            $axisAttributeCodes[] = $axisAttribute->getCode();
        }

        return $axisAttributeCodes;
    }

    /**
     * Generate the key value for array
     *
     * Generated keys will be in this format:
     *
     * [
     *    'blue_xxl_toyota' => [12, 14 ,18],
     *    'red_xs_ferrari'  => [13]
     * ]
     *
     * @param ProductInterface $product
     * @param array            $axisAttributeCodes
     *
     * @return string
     */
    protected function generateAxisCombinationKey(ProductInterface $product, array $axisAttributeCodes)
    {
        $attributeOptionCodes = [];
        foreach ($axisAttributeCodes as $attributeCode) {
            $attributeOption = $product->getValue($attributeCode)->getData();
            $attributeOptionCodes[] = $attributeOption->getCode();
        }

        return implode('_', $attributeOptionCodes);
    }

    /**
     * Fill the array with products id based on their variant axis combination as key
     *
     * @param ProductInterface $product
     * @param array            $productAttributeAxis
     * @param string           $keyCombination
     *
     * @return array
     */
    protected function fillDuplicateCombinationsArray(
        ProductInterface $product,
        array $productAttributeAxis,
        $keyCombination
    ) {
        if (array_key_exists($keyCombination, $productAttributeAxis)) {
            $productAttributeAxis[$keyCombination][] = $product->getId();
        } else {
            $productAttributeAxis[$keyCombination] = [$product->getId()];
        }

        return $productAttributeAxis;
    }

    /**
     * Returns the list of excluded ids by counting if there are more
     * than one product for the same combination of variant axis value
     *
     * @param array $productAttributeAxis
     *
     * @return array
     */
    protected function getExcludedProductIds(array $productAttributeAxis)
    {
        $excludedIds = [];
        foreach ($productAttributeAxis as $productId) {
            if (1 < count($productId)) {
                $excludedIds = array_merge($excludedIds, $productId);
            }
        }

        return $excludedIds;
    }

    /**
     * Add a warning message to the skipped products
     *
     * @param array $productAttributeAxis
     *
     * @return array
     */
    protected function addSkippedMessageForDuplicatedProducts(array $productAttributeAxis)
    {
        $excludedIds = $this->getExcludedProductIds($productAttributeAxis);
        if (!empty($excludedIds)) {
            $cursor = $this->getProductsCursor([['field' => 'id', 'operator' => 'IN', 'value' => $excludedIds]]);
            $paginator = $this->paginatorFactory->createPaginator($cursor);

            foreach ($paginator as $productsPage) {
                foreach ($productsPage as $product) {
                    $this->stepExecution->incrementSummaryInfo('skipped_products');
                    $this->stepExecution
                        ->addWarning(
                            'duplicated',
                            $this->translator->trans('add_to_variant_group.steps.cleaner.warning.description'),
                            [],
                            $product
                        );
                }

                $this->detachProducts($productsPage);
            }
        }

        return $excludedIds;
    }

    /**
     * It checks it products is in eligible products for the variant group and
     * build the array based on variant axis and product ids.
     *
     * @param PaginatorInterface $paginator
     * @param array              $eligibleProductIds
     * @param array              $axisAttributeCodes
     *
     * @return array
     */
    protected function filterDuplicateAxisCombinations(
        PaginatorInterface $paginator,
        array $eligibleProductIds,
        array $axisAttributeCodes
    ) {
        $productAttributeAxis = [];
        $acceptedIds = [];
        foreach ($paginator as $productsPage) {
            foreach ($productsPage as $product) {
                if (in_array($product->getId(), $eligibleProductIds)) {
                    $keyCombination = $this->generateAxisCombinationKey($product, $axisAttributeCodes);
                    $acceptedIds[] = $product->getId();
                    $productAttributeAxis = $this->fillDuplicateCombinationsArray(
                        $product,
                        $productAttributeAxis,
                        $keyCombination
                    );
                } else {
                    $this->stepExecution->incrementSummaryInfo('skipped_products');
                    $this->stepExecution
                        ->addWarning(
                            'excluded',
                            $this->translator->trans(
                                'pim_enrich.mass_edit_action.add-to-variant-group.already_in_variant_group_or_not_valid'
                            ),
                            [],
                            $product
                        );
                }
            }

            $this->detachProducts($productsPage);
        }

        return [$productAttributeAxis, $acceptedIds];
    }
}
