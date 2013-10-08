<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;

/**
 * Validator for valid variant group axis values constraint
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupMatrixValidator extends ConstraintValidator
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
     * @param ProductInterface $entity
     * @param Constraint       $constraint
     *
     * @return null
     */
    public function validate($entity, Constraint $constraint)
    {
        if (null !== $variantGroup = $entity->getVariantGroup()) {
            $criteria = array();
            foreach ($variantGroup->getAttributes() as $attribute) {
                $criteria[] = array(
                    'attribute' => $attribute,
                    'option'    => $entity->getValue($attribute->getCode())->getOption()
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
                $this->context->addViolation(
                    $constraint->message,
                    array(
                        '%values%'        => implode(', ', $values),
                        '%variant group%' => $variantGroup->getLabel()
                    )
                );
            }
        }
    }
}
