<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Symfony\Component\Validator\ValidatorInterface;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Oro\Bundle\BatchBundle\Item\ItemProcessorInterface;

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
     * Property for storing data during execution
     *
     * @var ArrayCollection
     */
    protected $data;

    /**
     * Property for storing valid entities during execution
     *
     * @var ArrayCollection
     */
    protected $entities;

    /**
     * Constructor
     *
     * @param EntityManager      $entityManager
     * @param ValidatorInterface $validator
     */
    public function __construct(
        EntityManager $entityManager,
        ValidatorInterface $validator
    ) {
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
     * Receives an array of entities and processes them
     *
     * @param mixed $data Data to be processed
     *
     * @return object[]
     */
    public function process($data)
    {
        $this->data = new ArrayCollection($data);
        $this->entities = new ArrayCollection();

        foreach ($this->data as $item) {
            $this->processItem($item);
        }

        return $this->entities->toArray();
    }

    /**
     * If the entity is valid, it is stored into the entities property
     *
     * @param array $item
     */
    abstract protected function processItem($item);
}
