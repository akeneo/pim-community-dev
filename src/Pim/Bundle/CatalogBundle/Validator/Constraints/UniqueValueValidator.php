<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Form\Form;

/**
 * Validator for unique value constraint
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UniqueValueValidator extends ConstraintValidator
{
    /** @var ProductManager */
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
     * Due to constraint guesser, the constraint is applied on :
     * - ProductValueInterface data when applied through form
     * - ProductValueInterface when applied directly through validator
     *
     * The constraint guesser should be re-worked in a future version to avoid such behavior
     *
     * @param ProductValueInterface|mixed $data
     * @param Constraint                  $constraint
     *
     * @see Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser\UniqueValueGuesser
     */
    public function validate($data, Constraint $constraint)
    {
        if (empty($data)) {
            return;
        }

        if (is_object($data) && $data instanceof ProductValueInterface) {
            $productValue = $data;
        } else {
            $productValue = $this->getProductValueFromForm();
        }

        if ($productValue instanceof ProductValueInterface && $this->productManager->valueExists($productValue)) {
            if ($productValue->getData() !== null && $productValue->getData() !== '') {
                $this->context->addViolation($constraint->message);
            }
        }
    }

    /**
     * Get product value from form
     *
     * @return ProductValueInterface|null
     */
    protected function getProductValueFromForm()
    {
        $root = $this->context->getRoot();
        if (!$root instanceof Form) {
            return;
        }

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
