<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Batch\Api\Product\Validation\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\InvalidArgumentException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExistingAttributesInValuesValidator extends ConstraintValidator
{
    ///** @var */
    //private $;

    ///**
    // * @param ObjectManager $objectManager
    // */
    //public function __construct(ObjectManager $objectManager)
    //{
    //    $this->objectManager = $objectManager;
    //}

    /**
     * {@inheritdoc}
     */
    public function validate($entity, Constraint $constraint)
    {
        if (!$constraint instanceof ExistingAttributesInValues) {
            throw new UnexpectedTypeException($constraint, ExistingAttributesInValues::class);
        }

        //if (null === $constraint->entityClass) {
        //    throw new InvalidArgumentException('You need to provide a valid entity class');
        //}
    }
}
