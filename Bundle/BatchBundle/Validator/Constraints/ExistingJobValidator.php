<?php

namespace Oro\Bundle\BatchBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use Oro\Bundle\BatchBundle\Connector\ConnectorRegistry;

/**
 * Validator for job instance entity
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExistingJobValidator extends ConstraintValidator
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
        if ($entity instanceof \Oro\Bundle\BatchBundle\Entity\JobInstance) {
            if (!$this->connectorRegistry->getJob($entity)) {
                $this->context->addViolation(
                    $constraint->message,
                    array('{{ job_type }}' => $entity->getType())
                );
            }
        }
    }
}
