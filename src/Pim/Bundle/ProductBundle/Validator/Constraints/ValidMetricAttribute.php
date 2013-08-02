<?php
namespace Pim\Bundle\ProductBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for metric attribute
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Annotation
 */
class ValidMetricAttribute extends Constraint
{
    /*
     * Violation message for invalid or missing metric type
     *
     * @var string
     */
    public $invalidFamilyMessage = 'Please specify a valid metric family';

    /*
     * Violation message for invalid or missing default metric unit
     *
     * @var string
     */
    public $invalidMetricUnitMessage = 'Please specify a valid metric unit';

    /**
     * {@inheritDoc}
     */
    public function validatedBy()
    {
        return 'pim_metric_attribute_validator';
    }

    /**
     * {@inheritDoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
