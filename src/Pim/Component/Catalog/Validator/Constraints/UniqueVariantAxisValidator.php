<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\ProductValue\OptionProductValueInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;
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
    /** @var ProductRepositoryInterface */
    protected $repository;

    /**
     * @param ProductRepositoryInterface $repository
     */
    public function __construct(ProductRepositoryInterface $repository)
    {
        $this->repository = $repository;
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
                $value = $product->getValue($code);
                $option = $value instanceof OptionProductValueInterface && null !== $value->getData() ?
                    $value->getData()->getCode() :
                    null;

                if (null === $option && !$attribute->isBackendTypeReferenceData()) {
                    $this->addEmptyAxisViolation(
                        $constraint,
                        $variantGroup->getLabel(),
                        $product->getIdentifier(),
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
        if (count($matches) !== 0) {
            $values = [];
            foreach ($criteria as $item) {
                $data = $item['attribute']->isBackendTypeReferenceData() ?
                    $item['referenceData']['data'] :
                    $item['option'];
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
            $isOption = $value instanceof OptionProductValueInterface;

            // we don't add criteria when option is null, as this check is performed by HasVariantAxesValidator
            if (null === $value ||
                (null === $value->getData() && $isOption && !$attribute->isBackendTypeReferenceData())
            ) {
                $this->addEmptyAxisViolation(
                    $constraint,
                    $variantGroup->getLabel(),
                    $product->getIdentifier(),
                    $attribute->getCode()
                );

                continue;
            }

            $current = ['attribute' => $attribute];

            if ($isOption && null !== $value->getData()) {
                $current['option'] = $value->getData();
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

        $result = [];
        foreach ($matchingProducts as $product) {
            if ($product->getId() !== $entity->getId()) {
                $result[] = $product;
            }
        }

        return $result;
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
     * Add existing combination violation
     *
     * @param Constraint $constraint
     * @param string     $variantLabel
     * @param string     $values
     */
    protected function addExistingCombinationViolation(Constraint $constraint, $variantLabel, $values)
    {
        $this->context->buildViolation(
            $constraint->message,
            [
                '%variant group%' => $variantLabel,
                '%values%'        => $values,
            ]
        )->atPath($constraint->propertyPath)->addViolation();
    }

    /**
     * @param Constraint $constraint
     * @param string     $variantLabel
     * @param string     $productIdentifier
     * @param string     $axisCode
     */
    protected function addEmptyAxisViolation(Constraint $constraint, $variantLabel, $productIdentifier, $axisCode)
    {
        $this->context->buildViolation(
            $constraint->missingAxisMessage,
            [
                '%group%'   => $variantLabel,
                '%product%' => $productIdentifier,
                '%axis%'    => $axisCode,
            ]
        )->atPath($constraint->propertyPath)->addViolation();
    }
}
