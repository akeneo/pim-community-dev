<?php

namespace Pim\Bundle\EnrichBundle\Connector\Item\MassEdit;

use Akeneo\Component\Batch\Item\DataInvalidItem;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Akeneo\Component\StorageUtils\Cursor\PaginatorInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Checks if there is no duplicate variant axis values between all products in the user selection.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupCleaner
{
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

    /**
     * @param ProductQueryBuilderFactoryInterface   $pqbFactory
     * @param PaginatorFactoryInterface             $paginatorFactory
     * @param ObjectDetacherInterface               $objectDetacher
     * @param IdentifiableObjectRepositoryInterface $groupRepository
     * @param ProductRepositoryInterface            $productRepository
     * @param TranslatorInterface                   $translator
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        PaginatorFactoryInterface $paginatorFactory,
        ObjectDetacherInterface $objectDetacher,
        IdentifiableObjectRepositoryInterface $groupRepository,
        ProductRepositoryInterface $productRepository,
        TranslatorInterface $translator
    ) {
        $this->pqbFactory        = $pqbFactory;
        $this->paginatorFactory  = $paginatorFactory;
        $this->objectDetacher    = $objectDetacher;
        $this->groupRepository   = $groupRepository;
        $this->productRepository = $productRepository;
        $this->translator        = $translator;
    }

    /**
     * Clean the filters to keep only non duplicated.
     * This method send "skipped" message for every duplicated product for a variant group.
     *
     * If there is no acceptable products, this method returns null, meaning no product is matching.
     *
     * @param StepExecution $stepExecution
     * @param array         $filters
     * @param array         $actions
     *
     * @return array|null
     */
    public function clean(StepExecution $stepExecution, $filters, $actions)
    {
        $variantGroupCode = $actions['value'];
        $variantGroup = $this->groupRepository->findOneByIdentifier($variantGroupCode);

        $axisAttributeCodes = $this->getAxisAttributeCodes($variantGroup);
        $eligibleProductIds = $this->productRepository->getEligibleProductIdsForVariantGroup($variantGroup->getId());

        $cursor = $this->getProductsCursor($filters);
        $paginator = $this->paginatorFactory->createPaginator($cursor);

        list($productAttributeAxis, $acceptedIds) = $this->filterDuplicateAxisCombinations(
            $stepExecution,
            $paginator,
            $eligibleProductIds,
            $axisAttributeCodes
        );

        $excludedIds = $this->addSkippedMessageForDuplicatedProducts($stepExecution, $productAttributeAxis);
        $acceptedIds = array_diff($acceptedIds, $excludedIds);

        if (0 === count($acceptedIds)) {
            return null;
        }

        return [[
            'field'    => 'id',
            'operator' => Operators::IN_LIST,
            'value'    => $acceptedIds
        ]];;
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
     * @return CursorInterface
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

        return $excludedIds;
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
            $cursor = $this->getProductsCursor([[
                'field'    => 'id',
                'operator' => Operators::IN_LIST,
                'value'    => $excludedIds
            ]]);
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
     * @param array              $eligibleProductIds
     * @param array              $axisAttributeCodes
     *
     * @return array
     */
    protected function filterDuplicateAxisCombinations(
        StepExecution $stepExecution,
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
