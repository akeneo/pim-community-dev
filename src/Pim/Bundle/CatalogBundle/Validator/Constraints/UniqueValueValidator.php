<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

use Pim\Bundle\CatalogBundle\Manager\ProductManager;

use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;

/**
 * Validator for unique value constraint
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UniqueValueValidator extends ConstraintValidator
{
    /**
     * @var ProductManager
     */
    protected $productManager;

    /**
     * Constructor
     *
     * @param ProductManager $productManager
     */
    public function __construct(ProductManager $productManager)
    {
        $this->productManager = $productManager;
    }

    /**
     * Constraint is applied on ProductValue data property.
     * That's why we use the current property path to guess the code
     * of the attribute to which the data belongs to.
     * @param object     $rawValue
     * @param Constraint $constraint
     *
     * @see Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser\UniqueValueGuesser
     */
    public function validate($rawValue, Constraint $constraint)
    {
        if (empty($rawValue)) {
            return;
        }

        $value = $this->getProductValue();

        if (($value instanceof ProductValueInterface) && ($this->productManager->valueExists($value))) {
            $this->context->addViolation($constraint->message);
        }

    }

    /**
     * Get productValue
     *
     * @return ProductValueInterface|null
     */
    private function getProductValue()
    {
        preg_match(
            '/children\[values\].children\[(\w+)\].children\[\w+\].data/',
            $this->context->getPropertyPath(),
            $matches
        );
        if (!isset($matches[1])) {
            return;
        }

        $product = $this->context->getRoot()->getData();
        if (!$product instanceof ProductInterface) {
            return;
        }

        $value = $product->getValue($matches[1]);

        if (false === $value) {
            return;
        }

        return $value;
    }
}
