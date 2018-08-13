<?php

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Single identifier attribute constraint
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SingleIdentifierAttribute extends Constraint
{
    /**
     * Violation message for already existing identifier attribute
     *
     * @var string
     */
    public $message = 'An identifier attribute already exists.';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_single_identifier_attribute_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
