<?php

namespace Oro\Bundle\WorkflowBundle\Serializer\Normalizer;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\WorkflowBundle\Exception\WorkflowException;
use Oro\Bundle\WorkflowBundle\Model\StepAttribute;
use Oro\Bundle\WorkflowBundle\Model\Workflow;

class AttributeNormalizer
{
    /**
     * Local cache for StepAttributes of Workflow
     *
     * @var Collection[]
     */
    protected $stepAttributesByWorkflow = array();

    /**
     * @var Workflow
     */
    protected $workflow;

    /**
     * @param ManagerRegistry $em
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
     * @return mixed
     */
    public function normalize(Workflow $workflow, $attributeName, $attributeValue)
    {
        $this->workflow = $workflow;
        $stepAttribute = $this->getStepAttribute($attributeName);
        if (!$stepAttribute) {
            return $attributeValue;
        }

        $entityManager = $this->getStepAttributeEntityManager($stepAttribute);
        if (null !== $attributeValue && $entityManager) {
            $ids = $this->getEntityIdentifierValues($stepAttribute, $attributeValue, $entityManager);
            if (count($ids) == 1) {
                $attributeValue = current($ids);
            } else {
                $attributeValue = $ids;
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
        $stepAttribute = $this->getStepAttribute($attributeName);
        if (!$stepAttribute) {
            return $attributeValue;
        }
        $entityManager = $this->getStepAttributeEntityManager($stepAttribute);
        if (null !== $attributeValue && $entityManager) {
            $attributeValue = $entityManager->getReference(
                $stepAttribute->getOption('entity_class'),
                $attributeValue
            );
        }
        return $attributeValue;
    }

    /**
     * Returs EntityManager if StepAttribute has option "entity_class", otherwise return null
     *
     * @param StepAttribute $stepAttribute
     * @return EntityManager|null
     * @throws WorkflowException If option 'entity_class' is not managed Doctrine entity
     */
    protected function getStepAttributeEntityManager(StepAttribute $stepAttribute)
    {
        $result = null;
        $entityClass = $stepAttribute->getOption('entity_class');
        if ($entityClass) {
            $result = $this->registry->getManagerForClass($entityClass);
            if (!$result) {
                throw new WorkflowException(
                    sprintf(
                        '"%s" attribute of workflow "%s" refers to "%s", but it\'s not managed entity class',
                        $stepAttribute->getName(),
                        $this->workflow->getName(),
                        $entityClass
                    )
                );
            }
        }
        return $result;
    }

    /**
     * Returns an array of identifiers of entity.
     *
     * @param StepAttribute $stepAttribute
     * @param object $entity
     * @param EntityManager $entityManager
     * @return array
     * @throws WorkflowException If cannot get entity ID
     */
    protected function getEntityIdentifierValues(StepAttribute $stepAttribute, $entity, EntityManager $entityManager)
    {
        $metadata = $entityManager->getClassMetadata($stepAttribute->getOption('entity_class'));
        $result = $metadata->getIdentifierValues($entity);

        if (!$result) {
            throw new WorkflowException(
                sprintf(
                    'Can\'t access id of entity in workflow data attribute "%s".'
                    . ' You must flush entity explicitly or set ID manually if you want to save it to workflow data.',
                    $stepAttribute->getName(),
                    $this->workflow->getName()
                )
            );
        }

        return $result;
    }

    /**
     * Get StepAttribute by name if it exist in workflow
     *
     * @param string $attributeName
     * @return StepAttribute|null
     */
    protected function getStepAttribute($attributeName)
    {
        return $this->getStepAttributes()->get($attributeName);
    }

    /**
     * Get collection of StepAttributes for current Workflow.
     *
     * This method caches results of Workflow::getStepAttributes method
     *
     * @return Collection
     */
    protected function getStepAttributes()
    {
        $workflowName = $this->workflow->getName();
        if (!isset($this->stepAttributesByWorkflow[$workflowName])) {
            $this->stepAttributesByWorkflow[$workflowName] = $this->workflow->getStepAttributes();
        }
        return $this->stepAttributesByWorkflow[$workflowName];
    }
}
