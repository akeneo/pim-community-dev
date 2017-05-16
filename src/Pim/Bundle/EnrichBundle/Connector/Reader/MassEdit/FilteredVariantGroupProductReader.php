<?php

namespace Pim\Bundle\EnrichBundle\Connector\Reader\MassEdit;

use Akeneo\Component\Batch\Item\DataInvalidItem;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Akeneo\Component\StorageUtils\Cursor\PaginatorInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Catalog\Converter\MetricConverter;
use Pim\Component\Catalog\Manager\CompletenessManager;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Pim\Component\Connector\Reader\Database\ProductReader;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Product reader for mass edit, skipping products not usable in variant group.
 *
 * This class is used to only skip duplicated elements and not throw exception during step execution.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilteredVariantGroupProductReader extends ProductReader
{
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

    /** @var array */
    protected $cleanedFilters;

    /**
     * @param ProductQueryBuilderFactoryInterface   $pqbFactory
     * @param ChannelRepositoryInterface            $channelRepository
     * @param CompletenessManager                   $completenessManager
     * @param MetricConverter                       $metricConverter
     * @param bool                                  $generateCompleteness
     * @param PaginatorFactoryInterface             $paginatorFactory
     * @param ObjectDetacherInterface               $objectDetacher
     * @param IdentifiableObjectRepositoryInterface $groupRepository
     * @param ProductRepositoryInterface            $productRepository
     * @param TranslatorInterface                   $translator
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ChannelRepositoryInterface $channelRepository,
        CompletenessManager $completenessManager,
        MetricConverter $metricConverter,
        $generateCompleteness,
        PaginatorFactoryInterface $paginatorFactory,
        ObjectDetacherInterface $objectDetacher,
        IdentifiableObjectRepositoryInterface $groupRepository,
        ProductRepositoryInterface $productRepository,
        TranslatorInterface $translator
    ) {
        parent::__construct(
            $pqbFactory,
            $channelRepository,
            $completenessManager,
            $metricConverter,
            $generateCompleteness
        );

        $this->paginatorFactory = $paginatorFactory;
        $this->objectDetacher = $objectDetacher;
        $this->groupRepository = $groupRepository;
        $this->productRepository = $productRepository;
        $this->translator = $translator;
    }

    /**
     * Get the configured filters, but remove duplicated products of variant groups before.
     *
     * {@inheritdoc}
     */
    protected function getConfiguredFilters()
    {
        if (null === $this->cleanedFilters) {
            $filters = parent::getConfiguredFilters();

            $jobParameters = $this->stepExecution->getJobParameters();
            $actions = $jobParameters->get('actions');

            $this->cleanedFilters = $this->clean($this->stepExecution, $filters, $actions);
        }

        return $this->cleanedFilters;
    }


    /**
     * Clean the filters to keep only non duplicated.
     * This method send "skipped" message for every duplicated product for a variant group.
     *
     * When all the selected products are skipped, there is no remaining products to mass-edit. The standard behaviour
     * should return a single filter like "id IN ()", which is equivalent to "nothing", and it's it is very poorly
     * managed by Doctrine.
     * Instead of returning this filter, we return a "is IS NULL" filter, which in this case is completely
     * equivalent to "nothing" (there can not be null ids).
     *
     * @param StepExecution $stepExecution
     * @param array         $filters
     * @param array         $actions
     *
     * @return array
     */
    public function clean(StepExecution $stepExecution, $filters, $actions)
    {
        $variantGroupCode = $actions['value'];
        $variantGroup = $this->groupRepository->findOneByIdentifier($variantGroupCode);

        $axisAttributeCodes = $this->getAxisAttributeCodes($variantGroup);
        $eligibleProducts = $this->productRepository->getEligibleProductsForVariantGroup($variantGroup->getId());

        $cursor = $this->getProductsCursor($filters);
        $paginator = $this->paginatorFactory->createPaginator($cursor);

        list($productAttributeAxis, $acceptedIds) = $this->filterDuplicateAxisCombinations(
            $stepExecution,
            $paginator,
            $eligibleProducts,
            $axisAttributeCodes
        );

        $excludedIds = $this->addSkippedMessageForDuplicatedProducts($stepExecution, $productAttributeAxis);
        $acceptedIds = array_diff($acceptedIds, $excludedIds);

        if (0 === count($acceptedIds)) {
            return [
                [
                    'field'    => 'id',
                    'operator' => Operators::IN_LIST,
                    'value'    => ['']
                ]
            ];
        }

        return [
            [
                'field'    => 'id',
                'operator' => Operators::IN_LIST,
                'value'    => $acceptedIds
            ]
        ];
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
     * @param GroupInterface $variantGroup
     *
     * @return array
     */
    protected function getAxisAttributeCodes(GroupInterface $variantGroup)
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

        return array_map(
            function ($id) {
                return (string)$id;
            },
            $excludedIds
        );
    }

    /**
     * Add a warning message to the skipped products
     *
     * @param StepExecution $stepExecution
     * @param array         $productAttributeAxis
     *
     * @return array
     */
    protected function addSkippedMessageForDuplicatedProducts(StepExecution $stepExecution, array $productAttributeAxis)
    {
        $excludedIds = $this->getExcludedProductIds($productAttributeAxis);
        if (!empty($excludedIds)) {
            $cursor = $this->getProductsCursor(
                [
                    [
                        'field'    => 'id',
                        'operator' => Operators::IN_LIST,
                        'value'    => $excludedIds
                    ]
                ]
            );
            $paginator = $this->paginatorFactory->createPaginator($cursor);

            foreach ($paginator as $productsPage) {
                foreach ($productsPage as $product) {
                    $stepExecution->incrementSummaryInfo('skipped_products');
                    $stepExecution
                        ->addWarning(
                            $this->translator->trans('add_to_variant_group.steps.cleaner.warning.description'),
                            [],
                            new DataInvalidItem($product)
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
     * @param StepExecution      $stepExecution
     * @param PaginatorInterface $paginator
     * @param CursorInterface    $eligibleProducts
     * @param array              $axisAttributeCodes
     *
     * @return array
     */
    protected function filterDuplicateAxisCombinations(
        StepExecution $stepExecution,
        PaginatorInterface $paginator,
        CursorInterface $eligibleProducts,
        array $axisAttributeCodes
    ) {
        $productAttributeAxis = [];
        $acceptedIds = [];

        $eligibleProductIds = [];
        foreach ($eligibleProducts as $eligibleProduct) {
            $eligibleProductIds[] = $eligibleProduct->getId();
        }

        foreach ($paginator as $productsPage) {
            foreach ($productsPage as $product) {
                if (in_array($product->getId(), $eligibleProductIds)) {
                    $keyCombination = $this->generateAxisCombinationKey($product, $axisAttributeCodes);
                    $acceptedIds[] = (string)$product->getId();
                    $productAttributeAxis = $this->fillDuplicateCombinationsArray(
                        $product,
                        $productAttributeAxis,
                        $keyCombination
                    );
                } else {
                    $stepExecution->incrementSummaryInfo('skipped_products');
                    $stepExecution
                        ->addWarning(
                            $this->translator->trans(
                                'pim_enrich.mass_edit_action.add-to-variant-group.already_in_variant_group_or_not_valid'
                            ),
                            [],
                            new DataInvalidItem($product)
                        );
                }
            }

            $this->detachProducts($productsPage);
        }

        return [$productAttributeAxis, $acceptedIds];
    }
}
