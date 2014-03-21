<?php

namespace Pim\Bundle\DataGridBundle\Extension\MassAction\Handler;

use Symfony\Component\Translation\TranslatorInterface;

use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\Actions\MassActionInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponse;

use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\Orm\EntityIdsHydrator;

/**
 * Mass delete action handler
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteMassActionHandler implements MassActionHandlerInterface
{
    /**
     * @var HydratorInterface $hydrator
     */
    protected $hydrator;

    /**
     * @var TranslatorInterface $translator
     */
    protected $translator;

    /**
     * @var string $responseMessage
     */
    protected $responseMessage = 'oro.grid.mass_action.delete.success_message';

    /**
     * Constructor
     *
     * @param HydratorInterface   $hydrator
     * @param TranslatorInterface $translator
     */
    public function __construct(HydratorInterface $hydrator, TranslatorInterface $translator)
    {
        $this->hydrator   = $hydrator;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(DatagridInterface $datagrid, MassActionInterface $massAction)
    {
        $datasource = $datagrid->getDatasource();
        $datasource->setHydrator($this->hydrator);

        // hydrator uses index by id
        $productIds = array_keys($datasource->getResults());

        try {
            $countProducts = $datasource->getRepository()->deleteFromIds($productIds);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            return new MassActionResponse(false, $this->translator->trans($errorMessage));
        }

        return $this->getResponse($massAction, $countProducts);
    }

    /**
     * Prepare mass action response
     *
     * @param MassActionInterface $massAction
     * @param integer             $entitiesCount
     *
     * @return MassActionResponse
     */
    protected function getResponse(MassActionInterface $massAction, $entitiesCount = 0)
    {
        $responseMessage = $massAction->getOptions()->offsetGetByPath(
            '[messages][success]',
            $this->responseMessage
        );

        return new MassActionResponse(
            true,
            $this->translator->trans($responseMessage),
            ['count' => $entitiesCount]
        );
    }
}
