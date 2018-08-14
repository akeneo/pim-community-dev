<?php

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for metric attribute
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValidMetric extends Constraint
{
    /**
     * Violation message for invalid or missing metric type
     *
     * @var string
     */
    public $familyMessage = 'Please specify a valid metric family';

    /**
     * Violation message for invalid or missing default metric unit
     *
     * @var string
     */
    public $unitMessage = 'Please specify a valid metric unit';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_metric_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
