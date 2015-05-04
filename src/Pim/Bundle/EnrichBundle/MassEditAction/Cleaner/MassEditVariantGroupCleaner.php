<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Cleaner;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Akeneo\Bundle\StorageUtilsBundle\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Cursor\PaginatorFactoryInterface;
use Akeneo\Component\StorageUtils\Cursor\PaginatorInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactoryInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\EnrichBundle\Entity\Repository\MassEditRepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * This step checks if there is no duplicate variant axis values between all products
 * in the use selection in the user selection
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MassEditVariantGroupCleaner extends AbstractConfigurableStepElement implements StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var MassEditRepositoryInterface */
    protected $massEditRepository;

    /** @var ObjectManager */
    protected $objectManager;

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
     * @param MassEditRepositoryInterface           $massEditRepository
     * @param ObjectManager                         $objectManager
     * @param IdentifiableObjectRepositoryInterface $groupRepository
     * @param ProductRepositoryInterface            $productRepository
     * @param TranslatorInterface                   $translator
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        PaginatorFactoryInterface $paginatorFactory,
        ObjectDetacherInterface $objectDetacher,
        MassEditRepositoryInterface $massEditRepository,
        ObjectManager $objectManager,
        IdentifiableObjectRepositoryInterface $groupRepository,
        ProductRepositoryInterface $productRepository,
        TranslatorInterface $translator
    ) {
        $this->pqbFactory         = $pqbFactory;
        $this->paginatorFactory   = $paginatorFactory;
        $this->objectDetacher     = $objectDetacher;
        $this->massEditRepository = $massEditRepository;
        $this->objectManager      = $objectManager;
        $this->groupRepository    = $groupRepository;
        $this->productRepository  = $productRepository;
        $this->translator         = $translator;
    }

    /**
     * @param array $configuration
     */
    public function execute(array $configuration)
    {
        $actions = $configuration['actions'];

        $variantGroupCode = $actions[0]['value'];

        $variantGroup = $this->groupRepository->findOneByIdentifier($variantGroupCode);

        $axisAttributeCodes = $this->getAxisAttributesCodes($variantGroup);
        $eligibleProductIds = $this->productRepository->getEligibleProductIdsForVariantGroup($variantGroup->getId());

        $cursor = $this->getProductsCursor($configuration['filters']);
        $paginator = $this->paginatorFactory->createPaginator($cursor);

        list($productAttributeAxis, $excludedIds, $ids) = $this->process(
            $paginator,
            $eligibleProductIds,
            $axisAttributeCodes
        );

        $excludedIds = $this->AddSkippedMessage($productAttributeAxis, $excludedIds);

        $ids = array_diff($ids, $excludedIds);

        $configuration['filters'] = [['field' => 'id', 'operator' => 'IN', 'value' => $ids]];

        if (0 === count($ids)) {
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
     * Return the job configuration
     *
     * @param string $configuration
     */
    protected function setJobConfiguration($configuration)
    {
        $jobExecution    = $this->stepExecution->getJobExecution();
        $massEditJobConf = $this->massEditRepository->findOneBy(['jobExecution' => $jobExecution]);
        $massEditJobConf->setConfiguration($configuration);

        $this->objectManager->persist($massEditJobConf);
        $this->objectManager->flush($massEditJobConf);
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
        $resolver->setOptional(['context']);
        $resolver->setDefaults([
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
    protected function getAxisAttributesCodes($variantGroup)
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
     * @param ProductInterface $product
     * @param array            $axisAttributeCodes
     *
     * @return string
     */
    protected function generateKey(ProductInterface $product, array $axisAttributeCodes)
    {
        $value = '';
        foreach ($axisAttributeCodes as $attributeCode) {
            $attributeOption = $product->getValue($attributeCode)->getData();
            $attributeOptionCode = $attributeOption->getCode();

            $value .= $attributeOptionCode.' ';
        }
        $value = rtrim($value, ' ');

        return $value;
    }

    /**
     * Fill the array with products id based on their variant axis combination as key
     *
     * @param ProductInterface $product
     * @param array            $productAttributeAxis
     * @param string           $str
     *
     * @return array
     */
    protected function fillArray(ProductInterface $product, array $productAttributeAxis, $str)
    {
        if (array_key_exists($str, $productAttributeAxis)) {
            $productAttributeAxis[$str][] = $product->getId();
        } else {
            $productAttributeAxis[$str] = [$product->getId()];
        }

        return $productAttributeAxis;
    }

    /**
     * Returns the list of excluded ids by counting if there are more
     * than one product for the same combination of variant axis value
     *
     * @param array $productAttributeAxis
     * @param array $excludedIds
     *
     * @return array
     */
    protected function getExcludedIds(array $productAttributeAxis, array $excludedIds)
    {
        foreach ($productAttributeAxis as $at) {
            if (1 < count($at)) {
                $excludedIds = array_merge($excludedIds, $at);
            }
        }

        return $excludedIds;
    }

    /**
     * Add a warning message to the skipped products
     *
     * @param array $productAttributeAxis
     * @param array $excludedIds
     *
     * @return array
     */
    protected function AddSkippedMessage(array $productAttributeAxis, array $excludedIds)
    {
        $excludedIds = $this->getExcludedIds($productAttributeAxis, $excludedIds);
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
     * @param PaginatorInterface $paginator
     * @param array              $eligibleProductIds
     * @param array              $axisAttributeCodes
     *
     * @return array
     */
    protected function process(PaginatorInterface $paginator, array $eligibleProductIds, array $axisAttributeCodes)
    {
        $productAttributeAxis = [];
        $excludedIds = [];
        $ids = [];
        foreach ($paginator as $productsPage) {
            foreach ($productsPage as $product) {
                if (in_array($product->getId(), $eligibleProductIds)) {
                    $str = $this->generateKey($product, $axisAttributeCodes);
                    $ids[] = $product->getId();
                    $productAttributeAxis = $this->fillArray($product, $productAttributeAxis, $str);
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

        return [$productAttributeAxis, $excludedIds, $ids];
    }
}
