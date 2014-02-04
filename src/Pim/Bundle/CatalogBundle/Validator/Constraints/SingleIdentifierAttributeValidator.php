<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Constraint;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

/**
 * Validator for single identifier attribute constraint
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SingleIdentifierAttributeValidator extends ConstraintValidator
{
    /**
     * Product manager
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
     * Don't allow creating an identifier attribute if one already exists
     *
     * @param AbstractAttribute $attribute
     * @param Constraint        $constraint
     */
    public function validate($attribute, Constraint $constraint)
    {
        if ($attribute->getAttributeType() === 'pim_catalog_identifier') {
            $identifier = $this->manager->getAttributeRepository()->findOneBy(
                array(
                    'attributeType' => 'pim_catalog_identifier'
                )
            );

            if ($identifier && $identifier->getId() !== $attribute->getId()) {
                $this->context->addViolationAt('attributeType', $constraint->message);
            }
        }
    }
}
