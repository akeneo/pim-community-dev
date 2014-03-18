<?php

namespace Pim\Bundle\DataGridBundle\Extension\MassAction;


use Symfony\Component\Translation\TranslatorInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionHandlerInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionMediatorInterface;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionResponse;


use Pim\Bundle\CatalogBundle\Model\ProductRepositoryInterface;

use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\Orm\EntityIdsHydrator;


/**
 * Product mass delete action handler
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductDeleteMassActionHandler implements MassActionHandlerInterface
{
    /**
     * @var TranslatorInterface $translator
     */
    protected $translator;

    /**
     * @var ProductRepositoryInterface $repository
     */
    protected $repository;

    /**
     * @var string $responseMessage
     */
    protected $responseMessage = 'oro.grid.mass_action.delete.success_message';

    /**
     * Constructor
     *
     * @param ProductRepositoryInterface $repository
     * @param TranslatorInterface        $translator
     */
    public function __construct(ProductRepositoryInterface $repository, TranslatorInterface $translator)
    {
        $this->repository = $repository;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(MassActionMediatorInterface $mediator)
    {
        $entityIdsHydrator = new EntityIdsHydrator();

        $datasource = $mediator->getDatagrid()->getDatasource();
        $datasource->setHydrator($entityIdsHydrator);

        // hydrator uses index by id
        $productIds = array_keys($datasource->getResults());

        try {
            $countProducts = $this->repository->deleteProducts($productIds);
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            return new MassActionResponse(false, $this->translator->trans($errorMessage));
        }

        return $this->getResponse($mediator, $countProducts);
    }

    /**
     * @param MassActionMediatorInterface $mediator
     * @param integer                     $entitiesCount
     *
     * @return MassActionResponse
     */
    protected function getResponse(MassActionMediatorInterface $mediator, $entitiesCount = 0)
    {
        $massAction      = $mediator->getMassAction();
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
