<?php
namespace Pim\Bundle\ProductBundle\Validator\Constraints;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttributeType;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Metric attribute validator
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ValidMetricAttributeValidator extends ConstraintValidator
{
    /**
     * @var array $measures
     */
    protected $measures;

    /**
     * Constructor
     *
     * @param Container $container
     */
    public function __construct($container)
    {
        $measures = $container->getParameter('oro_measure.measures_config');
        $this->measures = $measures['measures_config'];
    }

    /**
     * Validate metric type and default metric unit
     *
     * @param ProductAttribute $entity
     * @param Constraint       $constraint
     */
    public function validate($entity, Constraint $constraint)
    {
        if ($entity->getAttributeType() == AbstractAttributeType::TYPE_METRIC_CLASS) {
            $type = $entity->getMetricType();
            $unit = $entity->getDefaultMetricUnit();

            if (!array_key_exists($type, $this->measures)) {
                $this->context->addViolation($constraint->invalidMetricTypeMessage);
            } elseif (!array_key_exists($unit, $this->measures[$type]['units'])) {
                $this->context->addViolation($constraint->invalidMetricUnitMessage);
            }
        }
    }
}
