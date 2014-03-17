<?php

namespace Pim\Bundle\DataGridBundle\Extension\MassAction;

use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\Orm\EntityIdsHydrator;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Translation\TranslatorInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionMediatorInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponse;

/**
 * Overriden DeleteMassActionHandler to fix mass product deletion issue
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteMassActionHandler implements MassActionHandlerInterface
{
    /**
     * @var integer
     */
    const FLUSH_BATCH_SIZE = 100;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @var string
     */
    protected $responseMessage = 'oro.grid.mass_action.delete.success_message';

    /**
     * @param EntityManager       $entityManager
     * @param TranslatorInterface $translator
     */
    public function __construct(EntityManager $entityManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->translator    = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(MassActionMediatorInterface $mediator)
    {
        $iteration             = 0;


        $entityIdsHydrator = new EntityIdsHydrator();

        $datasource = $mediator->getDatagrid()->getDatasource();
        $datasource->setHydrator($entityIdsHydrator);

        $results = $datasource->getResults();

        var_dump($results);

//         $results = $mediator->getDatagrid()->getDatasource()->getQueryBuilder()->getQuery()->execute();

        foreach ($results as $result) {
//             $this->entityManager->remove($result);
            $iteration++;
            if ($iteration % self::FLUSH_BATCH_SIZE == 0) {
//                 $this->entityManager->flush();
            }
        }
//         $this->entityManager->flush();
//         $this->entityManager->clear();

        return $this->getResponse($mediator, $iteration);
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
        $responseMessage = $massAction->getOptions()->offsetGetByPath('[messages][success]', $this->responseMessage);

        $successful = $entitiesCount > 0;
        $options    = ['count' => $entitiesCount];

        return new MassActionResponse(
            $successful,
            $this->translator->transChoice(
                $responseMessage,
                $entitiesCount,
                ['%count%' => $entitiesCount]
            ),
            $options
        );
    }
}
