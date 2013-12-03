<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Symfony\Component\Validator\ValidatorInterface;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Oro\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;
use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

/**
 * Abstract entity processor to validate entity and create/update it
 *
 * Allow to bind an input data to an entity and validate it
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @abstract
 */
abstract class AbstractEntityProcessor extends AbstractConfigurableStepElement implements
    ItemProcessorInterface,
    StepExecutionAwareInterface
{
    /**
     * Entity manager
     *
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * Validator
     *
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var array
     */
    protected $identifiers;

    /**
     * Constructor
     *
     * @param EntityManager      $entityManager
     * @param ValidatorInterface $validator
     */
    public function __construct(EntityManager $entityManager, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->validator     = $validator;
        $this->identifiers   = array();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array();
    }

    /**
     * Validate the entity
     *
     * @param mixed $entity
     * @param array $item
     *
     * @throws InvalidItemException
     */
    public function validate($entity, $item)
    {
        $violations = $this->validator->validate($entity);
        if ($violations->count() > 0) {
            $messages = array();
            foreach ($violations as $violation) {
                $messages[] = (string) $violation;
            }
            $this->stepExecution->incrementSummaryInfo('skip');
            throw new InvalidItemException(implode(', ', $messages), $item);
        }

        $identifier = $this->getIdentifier($entity);
        if (in_array($identifier, $this->identifiers)) {
            $this->stepExecution->incrementSummaryInfo('skip');
            throw new InvalidItemException(
                sprintf('Duplicate, the entity "%s" has already been processed', $identifier),
                $item
            );
        }
        $this->identifiers[] = $identifier;
    }

    /**
     * Get entity identifier
     *
     * @param object $entity
     *
     * @return string
     */
    protected function getIdentifier($entity)
    {
        return $entity->getCode();
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
