<?php

namespace Oro\Bundle\ImportExportBundle\Strategy\Import;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Exception\LogicException;
use Oro\Bundle\ImportExportBundle\Exception\InvalidArgumentException;

class ImportStrategyHelper
{
    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param ValidatorInterface $validator
     * @param TranslatorInterface $translator
     */
    public function __construct(
        ManagerRegistry $managerRegistry,
        ValidatorInterface $validator,
        TranslatorInterface $translator
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->validator = $validator;
        $this->translator = $translator;
    }

    /**
     * @param string $entityClass
     * @return EntityManager
     * @throws LogicException
     */
    protected function getEntityManager($entityClass)
    {
        $entityManager = $this->managerRegistry->getManagerForClass($entityClass);
        if (!$entityManager) {
            throw new LogicException(
                sprintf('Can\'t find entity manager for %s', $entityClass)
            );
        }

        return $entityManager;
    }

    /**
     * @param object $basicEntity
     * @param object $importedEntity
     * @param array $excludedProperties
     * @throws InvalidArgumentException
     */
    public function importEntity($basicEntity, $importedEntity, array $excludedProperties = array())
    {
        $basicEntityClass = ClassUtils::getClass($basicEntity);
        if ($basicEntityClass != ClassUtils::getClass($importedEntity)) {
            throw new InvalidArgumentException('Basic and imported entities must be instances of the same class');
        }

        $entityMetadata = $this->getEntityManager($basicEntityClass)->getClassMetadata($basicEntityClass);
        $importedEntityProperties = array_diff(
            array_merge(
                $entityMetadata->getFieldNames(),
                $entityMetadata->getAssociationNames()
            ),
            $excludedProperties
        );

        foreach ($importedEntityProperties as $propertyName) {
            /** @var \ReflectionProperty $reflectionProperty */
            $reflectionProperty = $entityMetadata->getReflectionProperty($propertyName);
            $reflectionProperty->setAccessible(true); // just to make sure
            $importedValue = $reflectionProperty->getValue($importedEntity);
            $reflectionProperty->setValue($basicEntity, $importedValue);
        }
    }

    /**
     * Validate entity, returns list of errors or null
     *
     * @param object $entity
     * @return array|null
     */
    public function validateEntity($entity)
    {
        $violations = $this->validator->validate($entity);
        if (count($violations)) {
            $errors = array();
            /** @var ConstraintViolationInterface $violation */
            foreach ($violations as $violation) {
                $errors[] = sprintf(sprintf('%s: %s', $violation->getPropertyPath(), $violation->getMessage()));
            }
            return $errors;
        }

        return null;
    }

    /**
     * @param array $validationErrors
     * @param ContextInterface $context
     * @param string|null $errorPrefix
     */
    public function addValidationErrors(array $validationErrors, ContextInterface $context, $errorPrefix = null)
    {
        if (null === $errorPrefix) {
            $errorPrefix = $this->translator->trans(
                'oro.importexport.import_error %number%',
                array(
                    '%number%' => $context->getReadOffset()
                )
            );
        }
        foreach ($validationErrors as $validationError) {
            $context->addError($errorPrefix . ' ' . $validationError);
        }
    }
}
