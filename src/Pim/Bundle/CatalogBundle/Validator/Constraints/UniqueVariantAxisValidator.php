<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
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
    /** @var ProductManager $manager */
    protected $manager;

    /**
     * Constructor
     * @param ProductManager $manager
     */
    public function __construct(ProductManager $manager)
    {
        $this->manager = $manager;
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
     *
     * @return null
     */
    protected function validateProduct(ProductInterface $product, Constraint $constraint)
    {
        if (null === $product->getGroups()) {
            return;
        }

        foreach ($product->getGroups() as $variantGroup) {
            if ($variantGroup->getType()->isVariant()) {
                $criteria = $this->prepareQueryCriterias($variantGroup, $product);
                $matchingProducts = $this->getMatchingProductsForProduct($variantGroup, $product, $criteria);
                if (0 !== count($matchingProducts)) {
                    $values = [];
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
     * Prepare query criteria to validate variant group
     *
     * @param GroupInterface   $variantGroup
     * @param ProductInterface $product
     *
     * @return array
     */
    protected function prepareQueryCriterias(GroupInterface $variantGroup, ProductInterface $product)
    {
        $criteria = [];
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

        $repository = $this->manager->getProductRepository();
        $matchingProducts = $repository->findProductIdsForVariantGroup($variantGroup, $criteria);

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

        $repository = $this->manager->getProductRepository();

        return $repository->findAllForVariantGroup($variantGroup);
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
            [
                '%variant group%' => $variantLabel,
                '%values%'        => $values
            ]
        );
    }
}
