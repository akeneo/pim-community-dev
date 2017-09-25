<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
use Pim\Component\Catalog\Validator\UniqueAxesCombinationSet;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for unique variant group axes values constraint.
 * This validator should be called after HasVariantAxesValidator, once we know that the
 * product has all the axes of the variant.
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UniqueVariantAxisValidator extends ConstraintValidator
{
    /** @var ProductRepositoryInterface $repository */
    protected $repository;

    /** @var UniqueAxesCombinationSet */
    private $uniqueAxesCombinationSet;

    /**
     * @param ProductRepositoryInterface $repository
     * @param UniqueAxesCombinationSet   $uniqueAxesCombinationSet
     */
    public function __construct(
        ProductRepositoryInterface $repository,
        UniqueAxesCombinationSet $uniqueAxesCombinationSet = null
    ) {
        $this->repository = $repository;
        $this->uniqueAxesCombinationSet = $uniqueAxesCombinationSet;
    }

    /**
     * Don't allow having multiple products with same combination of values for axis of the variant group
     *
     * @param object     $entity
     * @param Constraint $constraint
     */
    public function validate($entity, Constraint $constraint)
    {
        if ($entity instanceof GroupInterface && $entity->getType()->isVariant()) {
            $this->validateVariantGroup($entity, $constraint);
        } elseif ($entity instanceof ProductInterface) {
            $this->validateProduct($entity, $constraint);
        }
    }

    /**
     * Validate variant group
     *
     * @param GroupInterface $variantGroup
     * @param Constraint     $constraint
     */
    protected function validateVariantGroup(GroupInterface $variantGroup, Constraint $constraint)
    {
        $existingCombinations = [];

        $products = $variantGroup->getProducts();
        if (null === $products) {
            $products = $this->getMatchingProductsForVariantGroup($variantGroup);
        }

        foreach ($products as $product) {
            $values = [];
            foreach ($variantGroup->getAxisAttributes() as $attribute) {
                $code = $attribute->getCode();
                $option = $product->getValue($code) ? (string)$product->getValue($code)->getOption() : null;

                if (null === $option && !$attribute->isBackendTypeReferenceData()) {
                    $this->addEmptyAxisViolation(
                        $constraint,
                        $variantGroup->getLabel(),
                        $product->getIdentifier()->getVarchar(),
                        $attribute->getCode()
                    );
                }
                $values[] = sprintf('%s: %s', $code, $option);
            }

            $combination = implode(', ', $values);
            if (in_array($combination, $existingCombinations)) {
                $this->addExistingCombinationViolation($constraint, $variantGroup->getLabel(), $combination);
            } else {
                $existingCombinations[] = $combination;
            }
        }
    }

    /**
     * Validate product
     *
     * @param ProductInterface $product
     * @param Constraint       $constraint
     */
    protected function validateProduct(ProductInterface $product, Constraint $constraint)
    {
        $group = $product->getVariantGroup();
        if (null === $group) {
            return;
        }

        $criteria = $this->prepareQueryCriterias($group, $product, $constraint);
        $matches = $this->getMatchingProductsForProduct($group, $product, $criteria);
        $alreadyProcessed = $this->hasAlreadyProcessedAxisValues($product, $criteria);

        if (0 !== count($matches) || $alreadyProcessed) {
            $values = [];
            foreach ($criteria as $item) {
                $data = $item['attribute']->isBackendTypeReferenceData()
                    ? $item['referenceData']['data']
                    : $item['option'];
                $values[] = sprintf('%s: %s', $item['attribute']->getCode(), (string)$data);
            }
            $this->addExistingCombinationViolation($constraint, $group->getLabel(), implode(', ', $values));
        }
    }

    /**
     * Prepare query criteria to validate variant group
     *
     * @param GroupInterface   $variantGroup
     * @param ProductInterface $product
     * @param Constraint       $constraint
     *
     * @return array
     */
    protected function prepareQueryCriterias(
        GroupInterface $variantGroup,
        ProductInterface $product,
        Constraint $constraint
    ) {
        $criteria = [];
        foreach ($variantGroup->getAxisAttributes() as $attribute) {
            $value = $product->getValue($attribute->getCode());
            // we don't add criteria when option is null, as this check is performed by HasVariantAxesValidator
            if (null === $value || (null === $value->getOption() && !$attribute->isBackendTypeReferenceData())) {
                $this->addEmptyAxisViolation(
                    $constraint,
                    $variantGroup->getLabel(),
                    $product->getIdentifier()->getVarchar(),
                    $attribute->getCode()
                );

                continue;
            }

            $current = ['attribute' => $attribute];

            if (null !== $value->getOption()) {
                $current['option'] = $value->getOption();
            } elseif ($attribute->isBackendTypeReferenceData()) {
                $current['referenceData'] = [
                    'name' => $attribute->getReferenceDataName(),
                    'data' => $value->getData(),
                ];
            }

            $criteria[] = $current;
        }

        return $criteria;
    }

    /**
     * Get matching products to validate product
     *
     * @param GroupInterface   $variantGroup the variant group
     * @param ProductInterface $entity       the product
     * @param array            $criteria     query criterias
     *
     * @return ProductInterface[]
     */
    protected function getMatchingProductsForProduct(
        GroupInterface $variantGroup,
        ProductInterface $entity,
        array $criteria = []
    ) {
        if (!$variantGroup->getId()) {
            return [];
        }

        $matchingProducts = $this->repository->findProductIdsForVariantGroup($variantGroup, $criteria);

        $matchingProducts = array_filter(
            $matchingProducts,
            function ($product) use ($entity) {
                return $product['id'] !== $entity->getId();
            }
        );

        return $matchingProducts;
    }

    /**
     * Get matching products for variant group
     *
     * @param GroupInterface $variantGroup the variant group
     *
     * @return ProductInterface[]
     */
    protected function getMatchingProductsForVariantGroup(GroupInterface $variantGroup)
    {
        if (!$variantGroup->getId()) {
            return [];
        }

        return $this->repository->findAllForVariantGroup($variantGroup);
    }

    /**
     * @param ProductInterface $product
     * @param array            $criteria
     *
     * @return bool
     */
    protected function hasAlreadyProcessedAxisValues(ProductInterface $product, array $criteria)
    {
        if (null === $this->uniqueAxesCombinationSet) {
            return false;
        }

        $axesData = [];
        foreach ($criteria as $axisData) {
            if (isset($axisData['option'])) {
                $axesData[] = sprintf(
                    '%s-%s',
                    $axisData['attribute']->getCode(),
                    $axisData['option']->getCode()
                );
            }

            if (isset($axisData['referenceData'])) {
                $axesData[] = sprintf(
                    '%s-%s-%s',
                    $axisData['attribute']->getCode(),
                    $axisData['referenceData']['name'],
                    $axisData['referenceData']['data']->getCode()
                );
            }
        }

        $combination = implode(',', $axesData);
        if ('' === str_replace(',', '', $combination)) {
            return false;
        }

        return false === $this->uniqueAxesCombinationSet->addCombination($product, $combination);
    }

    /**
     * Add existing combination violation
     *
     * @param UniqueVariantAxis $constraint
     * @param string            $variantLabel
     * @param string            $values
     */
    protected function addExistingCombinationViolation(UniqueVariantAxis $constraint, $variantLabel, $values)
    {
        $this->context->buildViolation(
            $constraint->message,
            [
                '%variant group%' => $variantLabel,
                '%values%' => $values,
            ]
        )->atPath($constraint->propertyPath)->addViolation();
    }

    /**
     * @param UniqueVariantAxis $constraint
     * @param string            $variantLabel
     * @param string            $productIdentifier
     * @param string            $axisCode
     */
    protected function addEmptyAxisViolation(
        UniqueVariantAxis $constraint,
        $variantLabel,
        $productIdentifier,
        $axisCode
    ) {
        $this->context->buildViolation(
            $constraint->missingAxisMessage,
            [
                '%group%' => $variantLabel,
                '%product%' => $productIdentifier,
                '%axis%' => $axisCode,
            ]
        )->atPath($constraint->propertyPath)->addViolation();
    }
}
