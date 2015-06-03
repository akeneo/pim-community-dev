<?php

namespace Pim\Bundle\DataGridBundle\Controller;

use Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Manager as DataGridManager;
use Pim\Bundle\DataGridBundle\Adapter\GridFilterAdapterInterface;
use Pim\Bundle\DataGridBundle\Datasource\ProductDatasource;
use Pim\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher;
use Pim\Bundle\ImportExportBundle\Entity\Repository\JobInstanceRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Products quick export
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductExportController
{
    /** @var Request */
    protected $request;

    /** @var MassActionDispatcher */
    protected $massActionDispatcher;

    /** @var GridFilterAdapterInterface */
    protected $gridFilterAdapter;

    /** @var JobInstanceRepository */
    protected $jobInstanceRepo;

    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var JobLauncherInterface */
    protected $jobLauncher;

    /** @var DataGridManager */
    protected $datagridManager;

    /**
     * @param Request                    $request
     * @param MassActionDispatcher       $massActionDispatcher
     * @param GridFilterAdapterInterface $gridFilterAdapter
     * @param JobInstanceRepository      $jobInstanceRepo
     * @param SecurityContextInterface   $securityContext
     * @param JobLauncherInterface       $jobLauncher
     * @param DataGridManager            $datagridManager
     */
    public function __construct(
        Request $request,
        MassActionDispatcher $massActionDispatcher,
        GridFilterAdapterInterface $gridFilterAdapter,
        JobInstanceRepository $jobInstanceRepo,
        SecurityContextInterface $securityContext,
        JobLauncherInterface $jobLauncher,
        DataGridManager $datagridManager
    ) {
        $this->request              = $request;
        $this->massActionDispatcher = $massActionDispatcher;
        $this->gridFilterAdapter    = $gridFilterAdapter;
        $this->jobInstanceRepo      = $jobInstanceRepo;
        $this->securityContext      = $securityContext;
        $this->jobLauncher          = $jobLauncher;
        $this->datagridManager      = $datagridManager;
    }

    /**
     * Launch the quick export
     *
     * @return Response
     */
    public function indexAction()
    {
        $jobInstance      = $this->jobInstanceRepo->findOneBy(['code' => 'csv_product_quick_export']);
        $filters          = $this->gridFilterAdapter->adapt($this->request);
        $rawConfiguration = addslashes(
            json_encode(
                [
                    'filters'     => $filters,
                    'mainContext' => $this->getContextParameters()
                ]
            )
        );

        $this->jobLauncher->launch($jobInstance, $this->getUser(), $rawConfiguration);

        return new Response();
    }

    /**
     * Get a user from the Security Context
     *
     * @return \Symfony\Component\Security\Core\User\UserInterface|null
     *
     * @see Symfony\Component\Security\Core\Authentication\Token\TokenInterface::getUser()
     */
    protected function getUser()
    {
        $token = $this->securityContext->getToken();
        if (null === $token || !is_object($user = $token->getUser())) {
            return null;
        }

        return $user;
    }

    /**
     * Get the context (locale and scope) from the datagrid
     *
     * @throws \LogicException If datasource is not a ProductDatasource
     *
     * @return string[] Returns [] || ['locale' => 'en_US', 'scope' => 'mobile']
     */
    protected function getContextParameters()
    {
        $datagridName = $this->request->get('gridName');
        $datagrid     = $this->datagridManager->getDatagrid($datagridName);
        $dataSource   = $datagrid->getDatasource();

        if (!$dataSource instanceof ProductDatasource) {
            throw new \LogicException('getContextParameters is only implemented for ProductDatasource');
        }

        $dataSourceParams = $dataSource->getParameters();
        $contextParams = [];
        if (is_array($dataSourceParams)) {
            $contextParams = [
                'locale' => $dataSourceParams['dataLocale'],
                'scope'  => $dataSourceParams['scopeCode']
            ];
        }

        return $contextParams;
    }
}
