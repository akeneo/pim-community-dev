<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Symfony\Component\Validator\ValidatorInterface;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Oro\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;

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
abstract class AbstractEntityProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface
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
     * Constructor
     *
     * @param EntityManager      $entityManager
     * @param ValidatorInterface $validator
     */
    public function __construct(EntityManager $entityManager, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->validator     = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array();
    }

    /**
     * Validate te entity
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
                $messages[]= (string) $violation;
            }

            throw new InvalidItemException(implode(', ', $messages), $item);
        }
    }
}
