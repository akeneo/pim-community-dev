<?php

namespace Oro\Bundle\GridBundle\Action\MassAction;

use Symfony\Component\Translation\TranslatorInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

use Oro\Bundle\GridBundle\Action\MassAction\MassActionMediatorInterface;
use Oro\Bundle\GridBundle\Action\MassAction\MassActionResponse;

class DeleteMassActionHandler implements MassActionHandlerInterface
{
    const FLUSH_BATCH_SIZE = 20;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var EntityRepository[]
     */
    protected $repositories = array();

    /**
     * @var string
     */
    protected $responseMessage = 'oro.grid.mass_action.delete.success_message';

    /**
     * @param EntityManager $entityManager
     * @param TranslatorInterface $translator
     */
    public function __construct(EntityManager $entityManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->translator    = $translator;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(MassActionMediatorInterface $mediator)
    {
        $iteration = 0;
        $entityName = null;
        $entityIdentifiedField = null;

        $results = $mediator->getResults();
        foreach ($results as $result) {
            $entity = $result->getRootEntity();
            if (!$entity) {
                if (!$entityName) {
                    $entityName = $this->getEntityName($mediator);
                }

                if (!$entityIdentifiedField) {
                    $entityIdentifiedField = $this->getEntityIdentifierField($mediator);
                }
                $entity = $this->getEntity($entityName, $result->getValue($entityIdentifiedField));
            }

            if ($entity) {
                $this->entityManager->remove($entity);

                $iteration++;
                if ($iteration % self::FLUSH_BATCH_SIZE == 0) {
                    $this->entityManager->flush();
                }
            }
        }

        if ($iteration % self::FLUSH_BATCH_SIZE > 0) {
            $this->entityManager->flush();
        }

        return $this->getResponse($mediator, $iteration);
    }

    /**
     * @param MassActionMediatorInterface $mediator
     * @param int $entitiesCount
     * @return MassActionResponse
     */
    protected function getResponse(MassActionMediatorInterface $mediator, $entitiesCount = 0)
    {
        $massAction = $mediator->getMassAction();
        $messages = $massAction->getOption('messages');
        $responseMessage = !empty($messages) && !empty($messages['success'])
                ? $messages['success']
                : $this->responseMessage;

        $successful = $entitiesCount > 0;
        $options = array('count' => $entitiesCount);

        return new MassActionResponse(
            $successful,
            $this->translator->transChoice(
                $responseMessage,
                $entitiesCount,
                array('%count%' => $entitiesCount)
            ),
            $options
        );
    }

    /**
     * @param MassActionMediatorInterface $mediator
     * @return string
     * @throws \LogicException
     */
    protected function getEntityName(MassActionMediatorInterface $mediator)
    {
        // TODO There should be a possibility to extract entity name from datagrid
        $massAction = $mediator->getMassAction();
        $entityName = $massAction->getOption('entity_name');
        if (!$entityName) {
            throw new \LogicException(sprintf('Mass action "%s" must define entity name', $massAction->getName()));
        }

        return $entityName;
    }

    /**
     * @param $entityName
     * @return EntityRepository
     * @throws \LogicException
     */
    protected function getEntityRepository($entityName)
    {
        if (empty($this->repositories[$entityName])) {
            if (!$this->entityManager->getClassMetadata($entityName)) {
                throw new \LogicException(sprintf('Entity "%s" is not manageable', $entityName));
            }

            $this->repositories[$entityName] = $this->entityManager->getRepository($entityName);
        }

        return $this->repositories[$entityName];
    }

    /**
     * @param MassActionMediatorInterface $mediator
     * @return string
     * @throws \LogicException
     */
    protected function getEntityIdentifierField(MassActionMediatorInterface $mediator)
    {
        $massAction = $mediator->getMassAction();
        $datagrid = $mediator->getDatagrid();
        if (!$datagrid) {
            throw new \LogicException(sprintf('Datagrid is required for mass action "%s"', $massAction->getName()));
        }

        $identifierField = $datagrid->getIdentifierField();
        if (!$identifierField) {
            throw new \LogicException(
                sprintf('Datagrid identifier field is required for mass action "%s"', $massAction->getName())
            );
        }

        return $identifierField;
    }

    /**
     * @param $entityName
     * @param $identifierValue
     * @return object|null
     * @throws \LogicException
     */
    protected function getEntity($entityName, $identifierValue)
    {
        if (!$identifierValue) {
            throw new \LogicException(
                sprintf('Identifier value is required for entity "%s"', $entityName)
            );
        }

        $repository = $this->getEntityRepository($entityName);

        return $repository->find($identifierValue);
    }
}
