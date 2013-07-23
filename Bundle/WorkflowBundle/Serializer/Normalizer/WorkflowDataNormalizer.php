<?php

namespace Oro\Bundle\WorkflowBundle\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\WorkflowBundle\Exception\WorkflowException;
use Oro\Bundle\WorkflowBundle\Serializer\WorkflowAwareSerializer;
use Oro\Bundle\WorkflowBundle\Model\StepAttribute;
use Oro\Bundle\WorkflowBundle\Model\Workflow;

/**
 * @TODO Cover with unit tests
 */
class WorkflowDataNormalizer extends SerializerAwareNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * Local cache for StepAttributes of Workflow
     *
     * @var Collection[]
     */
    protected $stepAttributesByWorkflow = array();

    /**
     * @var array
     */
    protected $callbacks = array();

    /**
     * @var array
     */
    protected $ignoredAttributes = array();

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @param ManagerRegistry $em
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Set normalization callbacks
     *
     * @param array $callbacks help normalize the result
     * @throws InvalidArgumentException if a non-callable callback is set
     */
    public function setCallbacks(array $callbacks)
    {
        foreach ($callbacks as $attribute => $callback) {
            if (!is_callable($callback)) {
                throw new InvalidArgumentException(
                    sprintf('The given callback for attribute "%s" is not callable.', $attribute)
                );
            }
        }
        $this->callbacks = $callbacks;
    }

    /**
     * Set ignored attributes for normalization
     *
     * @param array $ignoredAttributes
     */
    public function setIgnoredAttributes(array $ignoredAttributes)
    {
        $this->ignoredAttributes = $ignoredAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $attributes = array();
        foreach ($object as $attributeName => $attributeValue) {
            if (in_array($attributeName, $this->ignoredAttributes)) {
                continue;
            }

            if (array_key_exists($attributeName, $this->callbacks)) {
                $attributeValue = call_user_func($this->callbacks[$attributeName], $attributeValue);
            }

            $stepAttribute = $this->getStepAttributes()->get($attributeName);
            if ($stepAttribute) {
                $attributeValue = $this->normalizeStepAttribute($stepAttribute, $attributeValue);
            }

            if (null !== $attributeValue &&
                !is_scalar($attributeValue) &&
                $this->serializer instanceof NormalizerInterface
            ) {
                $attributeValue = $this->serializer->normalize($attributeValue, $format);
            }

            $attributes[$attributeName] = $attributeValue;
        }

        return $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $object = new $class;

        foreach ($data as $attributeName => $attributeValue) {
            $stepAttribute = $this->getStepAttributes()->get($attributeName);

            if ($stepAttribute) {
                $attributeValue = $this->denormalizeStepAttribute($stepAttribute, $attributeValue);
            }

            $object->set($attributeName, $attributeValue);
        }

        return $object;
    }

    /**
     * Normalizes a value of StepAttribute into a scalar
     *
     * @param StepAttribute $stepAttribute
     * @param mixed $attributeValue
     * @return mixed
     */
    public function normalizeStepAttribute(StepAttribute $stepAttribute, $attributeValue)
    {
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
     * Denormalizes value of StepAttribute back into it's model representation
     *
     * @param StepAttribute $stepAttribute
     * @param mixed $attributeValue
     * @return mixed
     */
    public function denormalizeStepAttribute(StepAttribute $stepAttribute, $attributeValue)
    {
        $entityManager = $this->getStepAttributeEntityManager($stepAttribute);
        if (null !== $attributeValue && $entityManager) {
            if (is_array($attributeValue) || is_scalar($attributeValue)) {
                $attributeValue = $entityManager->getReference(
                    $stepAttribute->getOption('entity_class'),
                    $attributeValue
                );
            } else {
                $attributeValue = null;
                /*throw new WorkflowException(
                    'Serialized data of "%s" attribute of workflow "%s" contains invalid entity ID.',
                    $stepAttribute->getName(),
                    $this->getWorkflow()->getName()
                );*/
            }
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
                    '"%s" attribute of workflow "%s" refers to "%s", but it\'s not managed entity class',
                    $stepAttribute->getName(),
                    $this->getWorkflow()->getName(),
                    $entityClass
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
        $metadata = $entityManager->getClassMetadata(get_class($entity));
        $result = $metadata->getIdentifierValues($entity);

        if (!$result) {
            throw new WorkflowException(
                sprintf(
                    'Can\'t access id of entity in workflow data attribute "%s".'
                    . ' You must flush entity explicitly or set ID manually if you want to save it to workflow data.',
                    $stepAttribute->getName(),
                    $this->getWorkflow()->getName()
                )
            );
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return is_object($data) && $this->supportsClass(get_class($data));
    }

    /**
     * {@inheritDoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $this->supportsClass($type);
    }

    /**
     * Checks if the given class is WorkflowData or it's ancestor.
     *
     * @param string $class
     * @return Boolean
     */
    protected function supportsClass($class)
    {
        $WorkflowDataClass = 'Oro\Bundle\WorkflowBundle\Model\WorkflowData';
        return $WorkflowDataClass == $class || in_array($WorkflowDataClass, class_parents($class));
    }

    /**
     * Get collection of StepAttributes for current Workflow
     *
     * @return Collection
     * @throws WorkflowException
     */
    protected function getStepAttributes()
    {
        $workflow = $this->getWorkflow();
        $workflowName = $workflow->getName();
        if (!isset($this->stepAttributesByWorkflow[$workflowName])) {
            $this->stepAttributesByWorkflow[$workflowName] = $workflow->getStepAttributes();
        }
        return $this->stepAttributesByWorkflow[$workflowName];
    }

    /**
     * Get Workflow
     *
     * @return Workflow
     * @throws WorkflowException
     */
    protected function getWorkflow()
    {
        if (!$this->serializer instanceof WorkflowAwareSerializer) {
            throw new WorkflowException(
                sprintf(
                    'Cannot get Workflow. Serializer must implement %s',
                    'Oro\Bundle\WorkflowBundle\Serializer\WorkflowAwareSerializer'
                )
            );
        }
        return $this->serializer->getWorkflow();
    }
}
