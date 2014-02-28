<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Entity\Group;

/**
 * Validator for unique variant group axis values constraint
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UniqueVariantAxisValidator extends ConstraintValidator
{
    /**
     * @var ProductManager $manager
     */
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
        if ($entity instanceof Group and $entity->getType()->isVariant()) {
            $this->validateVariantGroup($entity, $constraint);
        } elseif ($entity instanceof ProductInterface) {
            $this->validateProduct($entity, $constraint);
        }
    }

    /**
     * Validate variant group
     *
     * @param Group      $variantGroup
     * @param Constraint $constraint
     */
    protected function validateVariantGroup(Group $variantGroup, Constraint $constraint)
    {
        $existingCombinations = array();

        foreach ($variantGroup->getProducts() as $product) {
            $values = array();
            foreach ($variantGroup->getAttributes() as $attribute) {
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
     * @param ProductInterface $entity
     * @param Constraint       $constraint
     *
     * @return null
     */
    protected function validateProduct(ProductInterface $entity, Constraint $constraint)
    {
        if (null === $entity->getGroups()) {
            return;
        }

        foreach ($entity->getGroups() as $variantGroup) {
            if ($variantGroup->getType()->isVariant()) {
                $criteria = $this->prepareQueryCriterias($variantGroup, $entity);
                $matchingProducts = $this->getMatchingProducts($variantGroup, $entity, $criteria);
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
     * @param Group            $variantGroup
     * @param ProductInterface $entity
     *
     * @return array
     */
    protected function prepareQueryCriterias(Group $variantGroup, ProductInterface $entity)
    {
        $criteria = array();
        foreach ($variantGroup->getAttributes() as $attribute) {
            $value = $entity->getValue($attribute->getCode());
            $criteria[] = array(
                'attribute' => $attribute,
                'option'    => $value ? $value->getOption() : null,
            );
        }

        return $criteria;
    }

    /**
     * Get matching products
     *
     * @param Group            $variantGroup the variant group
     * @param ProductInterface $entity       the product
     * @param array            $criteria     query criterias
     *
     * @return ProductInterface[]
     */
    protected function getMatchingProducts(Group $variantGroup, ProductInterface $entity, array $criteria)
    {
        $repository = $this->manager->getFlexibleRepository();
        $matchingProducts = $repository->findAllForVariantGroup($variantGroup, $criteria);

        $matchingProducts = array_filter(
            $matchingProducts,
            function ($product) use ($entity) {
                return $product->getId() !== $entity->getId();
            }
        );

        return $matchingProducts;
    }

    /**
     * Add violation to the executioncontext
     *
     * @param Constraint $constraint
     * @param string     $productLabel
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
