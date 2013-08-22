<?php

namespace Oro\Bundle\WorkflowBundle\Serializer\Normalizer;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\Proxy;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\WorkflowBundle\Exception\SerializerException;
use Oro\Bundle\WorkflowBundle\Model\Attribute;
use Oro\Bundle\WorkflowBundle\Model\Workflow;

class EntityAttributeNormalizer implements AttributeNormalizer
{
    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize(Workflow $workflow, Attribute $attribute, $attributeValue)
    {
        if (null === $attributeValue) {
            return null;
        }
        $this->validateAttributeValue($workflow, $attribute, $attributeValue);

        if ($attributeValue instanceof Proxy && !$attributeValue->__isInitialized()) {
            $identifierProperty = new \ReflectionProperty(get_class($attributeValue), '_identifier');
            $identifierProperty->setAccessible(true);
            return $identifierProperty->getValue($attributeValue);
        } else {
            $em = $this->getEntityManager($workflow, $attribute);
            $metadata = $em->getClassMetadata($attribute->getOption('class'));
            return $metadata->getIdentifierValues($attributeValue);
        }
    }

    /**
     * Returns EntityManager for entity.
     *
     * @param Workflow $workflow
     * @param Attribute $attribute
     * @param mixed $attributeValue
     * @throws SerializerException
     */
    protected function validateAttributeValue(Workflow $workflow, Attribute $attribute, $attributeValue)
    {
        $expectedType = $attribute->getOption('class');
        if (!$attributeValue instanceof $expectedType) {
            throw new SerializerException(
                sprintf(
                    'Attribute "%s" of workflow "%s" must be an instance of "%s", but "%s" given',
                    $attribute->getName(),
                    $workflow->getName(),
                    $expectedType,
                    is_object($attributeValue) ? get_class($attributeValue) : gettype($attributeValue)
                )
            );
        }
    }

    /**
     * Returns EntityManager for entity.
     *
     * @param Workflow $workflow
     * @param Attribute $attribute
     * @return EntityManager
     * @throws SerializerException
     */
    protected function getEntityManager(Workflow $workflow, Attribute $attribute)
    {
        $entityClass = $attribute->getOption('class');
        $result = $this->registry->getManagerForClass($entityClass);
        if (!$result) {
            throw new SerializerException(
                sprintf(
                    'Attribute "%s" of workflow "%s" contains object of "%s", but it\'s not managed entity class',
                    $attribute->getName(),
                    $workflow->getName(),
                    $entityClass
                )
            );
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize(Workflow $workflow, Attribute $attribute, $attributeValue)
    {
        if (null === $attributeValue || !is_array($attributeValue)) {
            return null;
        }
        $em = $this->getEntityManager($workflow, $attribute);
        return $em->getReference($attribute->getOption('class'), $attributeValue);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization(Workflow $workflow, Attribute $attribute, $attributeValue)
    {
        return $attribute->getType() == 'entity';
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization(Workflow $workflow, Attribute $attribute, $attributeValue)
    {
        return $attribute->getType() == 'entity';
    }
}
