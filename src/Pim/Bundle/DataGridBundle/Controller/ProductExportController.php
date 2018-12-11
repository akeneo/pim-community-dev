<?php

namespace Pim\Bundle\DataGridBundle\Controller;

use Akeneo\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Manager as DataGridManager;
use Pim\Bundle\DataGridBundle\Adapter\GridFilterAdapterInterface;
use Pim\Bundle\DataGridBundle\Datasource\ProductDatasource;
use Pim\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Products quick export
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductExportController
{
    const DATETIME_FORMAT = 'Y-m-d_H:i:s';

    /** @var Request */
    protected $request;

    /** @var MassActionDispatcher */
    protected $massActionDispatcher;

    /** @var GridFilterAdapterInterface */
    protected $gridFilterAdapter;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $jobInstanceRepo;

    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var JobLauncherInterface */
    protected $jobLauncher;

    /** @var DataGridManager */
    protected $datagridManager;

    /**
     * @param Request                               $request
     * @param MassActionDispatcher                  $massActionDispatcher
     * @param GridFilterAdapterInterface            $gridFilterAdapter
     * @param IdentifiableObjectRepositoryInterface $jobInstanceRepo
     * @param TokenStorageInterface                 $tokenStorage
     * @param JobLauncherInterface                  $jobLauncher
     * @param DataGridManager                       $datagridManager
     */
    public function __construct(
        Request $request,
        MassActionDispatcher $massActionDispatcher,
        GridFilterAdapterInterface $gridFilterAdapter,
        IdentifiableObjectRepositoryInterface $jobInstanceRepo,
        TokenStorageInterface $tokenStorage,
        JobLauncherInterface $jobLauncher,
        DataGridManager $datagridManager
    ) {
        $this->request = $request;
        $this->massActionDispatcher = $massActionDispatcher;
        $this->gridFilterAdapter = $gridFilterAdapter;
        $this->jobInstanceRepo = $jobInstanceRepo;
        $this->tokenStorage = $tokenStorage;
        $this->jobLauncher = $jobLauncher;
        $this->datagridManager = $datagridManager;
    }

    /**
     * Launch the quick export
     *
     * @return Response
     */
    public function indexAction()
    {
        // If the parameter _displayedColumnOnly is set, it means it's a grid context. We didn't change the name of the
        // parameter to avoid BC.
        $withGridContext = (bool) $this->request->get('_displayedColumnsOnly');
        $jobCode = $this->request->get('_jobCode');
        $jobInstance = $this->jobInstanceRepo->findOneByIdentifier(['code' => $jobCode]);

        if (null === $jobInstance) {
            throw new \RuntimeException(sprintf('Jobinstance "%s" is not well configured', $jobCode));
        }

        $filters = $this->gridFilterAdapter->adapt($this->request);
        $rawParameters = $jobInstance->getRawParameters();
        $contextParameters = $this->getContextParameters();
        $rawParameters['filePath'] = $this->buildFilePath($rawParameters['filePath'], $contextParameters);
        $dynamicConfiguration = $contextParameters + ['filters' => $filters];

        if ($withGridContext) {
            $dynamicConfiguration['selected_locales'] = [$this->request->get('dataLocale')];

            $gridName = (null !== $this->request->get('gridName')) ? $this->request->get('gridName') : 'product-grid';
            if (isset($this->request->get($gridName)['_parameters'])) {
                $columns = explode(',', $this->request->get($gridName)['_parameters']['view']['columns']);
            } else {
                $columns = array_keys($this->datagridManager->getConfigurationForGrid($gridName)['columns']);
            }

            $dynamicConfiguration = array_merge(
                $dynamicConfiguration,
                [
                    'selected_properties' => $columns
                ]
            );
        }

        $configuration = array_merge($rawParameters, $dynamicConfiguration);

        $this->jobLauncher->launch($jobInstance, $this->getUser(), $configuration);

        return new Response();
    }

    /**
     * Get a user from the Security Context
     *
     * @return UserInterface|null
     *
     * @see \Symfony\Component\Security\Core\Authentication\Token\TokenInterface::getUser()
     */
    protected function getUser()
    {
        $token = $this->tokenStorage->getToken();
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
        $datagrid = $this->datagridManager->getDatagrid($datagridName);
        $dataSource = $datagrid->getDatasource();

        if (!$dataSource instanceof ProductDatasource) {
            throw new \LogicException('getContextParameters is only implemented for ProductDatasource');
        }

        $user = $this->getUser();
        $dataSourceParams = $dataSource->getParameters();
        $contextParams = [];
        if (is_array($dataSourceParams)) {
            $contextParams = [
                'locale'    => $dataSourceParams['dataLocale'],
                'scope'     => $dataSourceParams['scopeCode'],
                'ui_locale' => null !== $user ? $user->getUiLocale()->getCode() : $this->request->getDefaultLocale()
            ];
        }

        return $contextParams;
    }

    /**
     * Build file path to replace pattern like %locale%, %scope% by real data
     *
     * @param string $filePath
     * @param array  $contextParameters
     *
     * @return string
     */
    protected function buildFilePath($filePath, array $contextParameters)
    {
        $data = ['%datetime%' => date(static::DATETIME_FORMAT)];
        foreach ($contextParameters as $key => $value) {
            $data['%' . $key . '%'] = $value;
        }

        return strtr($filePath, $data);
    }
}
