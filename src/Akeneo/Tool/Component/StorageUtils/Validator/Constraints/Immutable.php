<?php

namespace Akeneo\Tool\Component\StorageUtils\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for an immutable property of an object
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Immutable extends Constraint
{
    /**
     * The immutable properties
     *
     * @var string[]
     */
    public $properties;

    /**
     * Violation message for changed property
     *
     * @var string
     */
    public $message = 'This property cannot be changed.';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_immutable_validator';
    }
}
