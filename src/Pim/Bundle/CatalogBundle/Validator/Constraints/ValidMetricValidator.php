<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Metric attribute validator
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValidMetricValidator extends ConstraintValidator
{
    /**
     * @var array $measures
     */
    protected $measures;

    /**
     * Constructor
     *
     * @param array $measures
     */
    public function __construct($measures)
    {
        $this->measures = $measures['measures_config'];
    }

    /**
     * Validate metric type and default metric unit
     *
     * @param ProductAttributeInterface $entity
     * @param Constraint                $constraint
     */
    public function validate($entity, Constraint $constraint)
    {
        $type = $entity->getMetricFamily();
        $unit = $entity->getDefaultMetricUnit();

        if (!array_key_exists($type, $this->measures)) {
            $this->context->addViolationAt('metricFamily', $constraint->familyMessage);
        } elseif (!array_key_exists($unit, $this->measures[$type]['units'])) {
            $this->context->addViolationAt('defaultMetricUnit', $constraint->unitMessage);
        }
    }
}
