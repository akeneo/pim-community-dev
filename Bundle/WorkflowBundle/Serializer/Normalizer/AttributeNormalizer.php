<?php

namespace Oro\Bundle\WorkflowBundle\Serializer\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\WorkflowBundle\Exception\WorkflowException;
use Oro\Bundle\WorkflowBundle\Model\Attribute;
use Oro\Bundle\WorkflowBundle\Model\Workflow;
use Oro\Bundle\WorkflowBundle\Serializer\EntityReference;

class AttributeNormalizer
{
    /**
     * Local cache for Attributes of Workflow
     *
     * @var Collection[]
     */
    protected $stepAttributesByWorkflow = array();

    /**
     * @var Workflow
     */
    protected $workflow;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Normalizes a value of attribute into a scalar
     *
     * @param Workflow $workflow
     * @param string $attributeName
     * @param mixed $attributeValue
     * @throws WorkflowException
     * @return mixed
     */
    public function normalize(Workflow $workflow, $attributeName, $attributeValue)
    {
        $this->workflow = $workflow;

        if (is_object($attributeValue)) {
            $entityManager = $this->getEntityManager(get_class($attributeValue), $attributeName);
            if ($entityManager) {
                $entityReference = new EntityReference();
                $entityReference->initByEntity($entityManager, $attributeValue);
                $this->assertAttributeEntity($attributeName, $entityReference->getClassName());
                $attributeValue = array(
                    'entity_class' => $entityReference->getClassName(),
                    'ids' => $entityReference->getIds()
                );
            }
        }
        return $attributeValue;
    }

    /**
     * Denormalizes value of attribute back into it's model representation
     *
     * @param Workflow $workflow
     * @param string $attributeName
     * @param mixed $attributeValue
     * @return mixed
     */
    public function denormalize(Workflow $workflow, $attributeName, $attributeValue)
    {
        $this->workflow = $workflow;

        if (is_array($attributeValue) && isset($attributeValue['entity_class']) && isset($attributeValue['ids'])) {
            $entityReference = new EntityReference();
            $entityReference
                ->setClassName($attributeValue['entity_class'])
                ->setIds($attributeValue['ids']);

            $this->assertAttributeEntity($attributeName, $entityReference->getClassName());
            $entityManager = $this->getEntityManager($entityReference->getClassName(), $attributeName);
            if ($entityManager) {
                $attributeValue = $entityManager->getReference(
                    $entityReference->getClassName(),
                    $entityReference->getIds()
                );
            }
        }
        return $attributeValue;
    }

    /**
     * Returns EntityManager for entity.
     *
     * @param string $entityClass
     * @param string $attributeName
     * @throws \Oro\Bundle\WorkflowBundle\Exception\WorkflowException
     * @return EntityManager|null
     */
    protected function getEntityManager($entityClass, $attributeName)
    {
        $result = null;

        $result = $this->registry->getManagerForClass($entityClass);
        if (!$result) {
            $stepAttribute = $this->getAttribute($attributeName);
            if ($stepAttribute && $stepAttribute->getOption('entity_class')) {
                throw new WorkflowException(
                    sprintf(
                        'Workflow "%s" contains "%s", but it\'s not managed entity class',
                        $this->workflow->getName(),
                        $entityClass
                    )
                );
            }
        }

        return $result;
    }

    /**
     * Get Attribute by name if it exist in workflow
     *
     * @param string $attributeName
     * @return Attribute|null
     */
    protected function getAttribute($attributeName)
    {
        return $this->getAttributes()->get($attributeName);
    }

    /**
     * Get collection of Attributes for current Workflow.
     *
     * This method caches results of Workflow::getAttributes method
     *
     * @return Collection
     */
    protected function getAttributes()
    {
        $workflowName = $this->workflow->getName();
        if (!isset($this->stepAttributesByWorkflow[$workflowName])) {
            $this->stepAttributesByWorkflow[$workflowName] = $this->workflow->getAttributes();
        }
        return isset($this->stepAttributesByWorkflow[$workflowName])
            ? $this->stepAttributesByWorkflow[$workflowName]
            : new ArrayCollection();
    }

    /**
     * @param string $attributeName
     * @param string $entityClass
     * @throws WorkflowException
     */
    protected function assertAttributeEntity($attributeName, $entityClass)
    {
        $stepAttribute = $this->getAttribute($attributeName);
        if ($stepAttribute
            && $stepAttribute->getOption('entity_class')
            && $entityClass != $stepAttribute->getOption('entity_class')
        ) {
            throw new WorkflowException(
                sprintf(
                    'Attribute "%s" defined to use "%s" but "%s" given.',
                    $stepAttribute->getName(),
                    $stepAttribute->getOption('entity_class'),
                    $entityClass
                )
            );
        }
    }
}
