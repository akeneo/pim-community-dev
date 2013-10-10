<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Entity\VariantGroup;

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
        if ($entity instanceof VariantGroup) {
            $this->validateVariantGroup($entity, $constraint);
        } elseif ($entity instanceof ProductInterface) {
            $this->validateProduct($entity, $constraint);
        }
    }

    /**
     * Validate variant group
     *
     * @param VariantGroup $variantGroup
     * @param Constraint   $constraint
     */
    protected function validateVariantGroup(VariantGroup $variantGroup, Constraint $constraint)
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
                $this->addViolation($constraint, $combination, $variantGroup->getLabel());
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
        if (null !== $variantGroup = $entity->getVariantGroup()) {
            $criteria = array();
            foreach ($variantGroup->getAttributes() as $attribute) {
                $value = $entity->getValue($attribute->getCode());
                $criteria[] = array(
                    'attribute' => $attribute,
                    'option'    => $value ? $value->getOption() : null,
                );
            }

            $repository = $this->manager->getFlexibleRepository();
            $matchingProducts = $repository->findAllForVariantGroup($variantGroup, $criteria);

            $matchingProducts = array_filter(
                $matchingProducts,
                function ($product) use ($entity) {
                    return $product->getId() !== $entity->getId();
                }
            );

            if (count($matchingProducts) !== 0) {
                $values = array();
                foreach ($criteria as $item) {
                    $values[] = sprintf('%s: %s', $item['attribute']->getCode(), (string) $item['option']);
                }
                $this->addViolation($constraint, implode(', ', $values), $variantGroup->getLabel());
            }
        }
    }

    /**
     * Add violation to the executioncontext
     *
     * @param Constraint $constraint
     * @param string     $values
     * @param string     $variantLabel
     */
    protected function addViolation(Constraint $constraint, $values, $variantLabel)
    {
        $this->context->addViolation(
            $constraint->message,
            array(
                '%values%'        => $values,
                '%variant group%' => $variantLabel
            )
        );
    }
}
