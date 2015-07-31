<?php

namespace Pim\Bundle\ImportExportBundle\Validator\Constraints;

use Akeneo\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for job instance entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceValidator extends ConstraintValidator
{
    /**
     * @var ConnectorRegistry
     */
    protected $connectorRegistry;

    /**
     * Constructor
     *
     * @param ConnectorRegistry $connectorRegistry
     */
    public function __construct(ConnectorRegistry $connectorRegistry)
    {
        $this->connectorRegistry = $connectorRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($entity, Constraint $constraint)
    {
        if ($entity instanceof \Akeneo\Bundle\BatchBundle\Entity\JobInstance) {
            if (!$this->connectorRegistry->getJob($entity)) {
                $this->context->buildViolation(
                    $constraint->message,
                    ['%job_type%' => $entity->getType()]
                )->atPath($constraint->property)
                ->addViolation();
            }
        }
    }
}
