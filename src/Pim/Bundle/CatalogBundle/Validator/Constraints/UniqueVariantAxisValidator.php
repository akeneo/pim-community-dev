<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
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

    /**
     * Constructor
     *
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
        $existingCombinations = array();

        $products = $variantGroup->getProducts();
        if (null === $products) {
            $products = $this->getMatchingProducts($variantGroup);
        }
        foreach ($products as $product) {
            $values = array();
            foreach ($variantGroup->getAxisAttributes() as $attribute) {
                $code = $attribute->getCode();
                $option = $product->getValue($code) ? (string) $product->getValue($code)->getOption() : '';
                $values[] = sprintf('%s: %s', $code, $option);
            }
            $combination = implode(', ', $values);

            if (in_array($combination, $existingCombinations)) {
                $this->addViolation($constraint, $variantGroup->getLabel(), $combination);
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
        if (null === $product->getGroups()) {
            return;
        }

        foreach ($product->getGroups() as $variantGroup) {
            if ($variantGroup->getType()->isVariant()) {
                $criteria = $this->prepareQueryCriterias($variantGroup, $product);
                $matchingProducts = $this->getMatchingProducts($variantGroup, $product, $criteria);
                if (count($matchingProducts) !== 0) {
                    $values = array();
                    foreach ($criteria as $item) {
                        $values[] = sprintf('%s: %s', $item['attribute']->getCode(), (string) $item['option']);
                    }
                    $this->addViolation(
                        $constraint,
                        $variantGroup->getLabel(),
                        implode(', ', $values)
                    );
                }
            }
        }
    }

    /**
     * Prepare query criteria for variant group
     *
     * @param GroupInterface   $variantGroup
     * @param ProductInterface $product
     *
     * @return array
     */
    protected function prepareQueryCriterias(GroupInterface $variantGroup, ProductInterface $product)
    {
        $criteria = array();
        foreach ($variantGroup->getAxisAttributes() as $attribute) {
            $value = $product->getValue($attribute->getCode());
            // we don't add criteria when option is null, as this check is performed by HasVariantAxesValidator
            if (null !== $value && null !== $value->getOption()) {
                $criteria[] = [
                    'attribute' => $attribute,
                    'option'    => $value->getOption(),
                ];
            }
        }

        return $criteria;
    }

    /**
     * Get matching products
     *
     * @param GroupInterface   $variantGroup the variant group
     * @param ProductInterface $entity       the product
     * @param array            $criteria     query criterias
     *
     * @return ProductInterface[]
     */
    protected function getMatchingProducts(
        GroupInterface $variantGroup,
        ProductInterface $entity = null,
        array $criteria = []
    ) {
        if (!$variantGroup->getId()) {
            return [];
        }

        $matchingProducts = $this->repository->findAllForVariantGroup($variantGroup, $criteria);

        if ($entity) {
            $matchingProducts = array_filter(
                $matchingProducts,
                function ($product) use ($entity) {
                    return $product->getId() !== $entity->getId();
                }
            );
        }

        return $matchingProducts;
    }

    /**
     * Add violation to the executioncontext
     *
     * @param Constraint $constraint
     * @param string     $variantLabel
     * @param string     $values
     */
    protected function addViolation(Constraint $constraint, $variantLabel, $values)
    {
        $this->context->addViolation(
            $constraint->message,
            array(
                '%variant group%' => $variantLabel,
                '%values%'        => $values
            )
        );
    }
}
