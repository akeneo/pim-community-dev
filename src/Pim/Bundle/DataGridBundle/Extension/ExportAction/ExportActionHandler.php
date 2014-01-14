<?php

namespace Pim\Bundle\DataGridBundle\Extension\ExportAction;

use Symfony\Component\Serializer\SerializerInterface;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\Parameter;

use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionMediatorInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\DataGridBundle\Datasource\Orm\ConstantPagerIterableResult;
use Oro\Bundle\DataGridBundle\Datasource\Orm\IterableResultInterface;

/**
 * Export action handler
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExportActionHandler implements MassActionHandlerInterface
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @param EntityManager       $entityManager
     * @param SerializerInterface $serializer
     */
    public function __construct(EntityManager $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer    = $serializer;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(MassActionMediatorInterface $mediator)
    {
        var_dump('got here'); die;
        $iteration             = 0;
        $entityName            = null;
        $entityIdentifiedField = null;

        $results = $this->prepareIterableResult($mediator->getResults());
        $results->setBufferSize(100);

        // batch remove should be processed in transaction
        $this->entityManager->beginTransaction();
        try {
            foreach ($results as $result) {
                /** @var $result ResultRecordInterface */
                $entity = $result->getRootEntity();
                if (!$entity) {
                    // no entity in result record, it should be extracted from DB
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
                    if ($iteration % 100 == 0) {
                        $this->entityManager->flush();
                        $this->entityManager->clear();
                    }
                }
            }

            if ($iteration % 100 > 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }

            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }

        return $this->getResponse($mediator, $iteration);
    }

    /**
     * @param IterableResultInterface $result
     *
     * @return ConstantPagerIterableResult
     */
    protected function prepareIterableResult(IterableResultInterface $result)
    {
        $results =  new ConstantPagerIterableResult($result->getSource());
        $params = [];
        /** @var Parameter $param */
        foreach ($result->getSource()->getParameters() as $param) {
            $params[$param->getName()] = $param->getValue();
        }
        $results->setParameters($params);

        return $results;
    }

    /**
     * @param MassActionMediatorInterface $mediator
     * @param int                         $entitiesCount
     *
     * @return MassActionResponse
     */
    protected function getResponse(MassActionMediatorInterface $mediator, $entitiesCount = 0)
    {
        $massAction      = $mediator->getMassAction();
        $responseMessage = $massAction->getOptions()->offsetGetByPath('[messages][success]', '$this->responseMessage');

        $successful = $entitiesCount > 0;
        $options    = ['count' => $entitiesCount];

        return new MassActionResponse(
            $successful,
            $responseMessage,
            $entitiesCount,
            ['%count%' => $entitiesCount],
            $options
        );
    }

    /**
     * @param MassActionMediatorInterface $mediator
     *
     * @return string
     * @throws \LogicException
     */
    protected function getEntityName(MassActionMediatorInterface $mediator)
    {
        $massAction = $mediator->getMassAction();
        $entityName = $massAction->getOptions()->offsetGet('entity_name');
        if (!$entityName) {
            throw new \LogicException(sprintf('Mass action "%s" must define entity name', $massAction->getName()));
        }

        return $entityName;
    }

    /**
     * @param MassActionMediatorInterface $mediator
     *
     * @throws \LogicException
     * @return string
     */
    protected function getEntityIdentifierField(MassActionMediatorInterface $mediator)
    {
        $massAction = $mediator->getMassAction();
        $identifier = $massAction->getOptions()->offsetGet('data_identifier');
        if (!$identifier) {
            throw new \LogicException(sprintf('Mass action "%s" must define identifier name', $massAction->getName()));
        }

        // if we ask identifier that's means that we have plain data in array
        // so we will just use column name without entity alias
        if (strpos('.', $identifier) !== -1) {
            $parts      = explode('.', $identifier);
            $identifier = end($parts);
        }

        return $identifier;
    }

    /**
     * @param string $entityName
     * @param mixed  $identifierValue
     *
     * @return object
     */
    protected function getEntity($entityName, $identifierValue)
    {
        return $this->entityManager->getReference($entityName, $identifierValue);
    }
}
