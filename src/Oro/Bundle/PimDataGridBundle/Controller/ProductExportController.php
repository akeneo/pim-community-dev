<?php

namespace Oro\Bundle\PimDataGridBundle\Controller;

use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Oro\Bundle\DataGridBundle\Datagrid\Manager as DataGridManager;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;
use Oro\Bundle\PimDataGridBundle\Adapter\GridFilterAdapterInterface;
use Oro\Bundle\PimDataGridBundle\Datasource\ProductAndProductModelDatasource;
use Oro\Bundle\PimDataGridBundle\Datasource\ProductDatasource;
use Oro\Bundle\PimDataGridBundle\Extension\MassAction\MassActionDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
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
    private const FILE_PATH_KEYS = ['filePath', 'filePathProduct', 'filePathProductModel'];

    protected RequestStack $requestStack;
    protected MassActionDispatcher $massActionDispatcher;
    protected GridFilterAdapterInterface $gridFilterAdapter;
    protected IdentifiableObjectRepositoryInterface $jobInstanceRepo;
    protected TokenStorageInterface $tokenStorage;
    protected JobLauncherInterface $jobLauncher;
    protected DataGridManager $datagridManager;
    protected MassActionParametersParser $parameterParser;

    public function __construct(
        RequestStack $requestStack,
        MassActionDispatcher $massActionDispatcher,
        GridFilterAdapterInterface $gridFilterAdapter,
        IdentifiableObjectRepositoryInterface $jobInstanceRepo,
        TokenStorageInterface $tokenStorage,
        JobLauncherInterface $jobLauncher,
        DataGridManager $datagridManager,
        MassActionParametersParser $parameterParser
    ) {
        $this->requestStack = $requestStack;
        $this->massActionDispatcher = $massActionDispatcher;
        $this->gridFilterAdapter = $gridFilterAdapter;
        $this->jobInstanceRepo = $jobInstanceRepo;
        $this->tokenStorage = $tokenStorage;
        $this->jobLauncher = $jobLauncher;
        $this->datagridManager = $datagridManager;
        $this->parameterParser = $parameterParser;
    }

    /**
     * Launch the quick export
     */
    public function indexAction(Request $request): JsonResponse
    {
        // If the parameter _displayedColumnOnly is set, it means it's a grid context. We didn't change the name of the
        // parameter to avoid BC.
        $withGridContext = (bool) $request->get('_displayedColumnsOnly');
        $withLabels = (bool) $request->get('_withLabels');
        $withMedia = (bool) $request->get('_withMedia', true);
        $fileLocale = $request->get('_fileLocale');
        $jobCode = $request->get('_jobCode');
        $jobInstance = $this->jobInstanceRepo->findOneByIdentifier(['code' => $jobCode]);

        if (null === $jobInstance) {
            throw new \RuntimeException(sprintf('Jobinstance "%s" is not well configured', $jobCode));
        }

        $parameters = $this->parameterParser->parse($request);
        $filters = $this->gridFilterAdapter->adapt($parameters);
        $rawParameters = $jobInstance->getRawParameters();
        $contextParameters = $this->getContextParameters($request);
        $dynamicConfiguration = $contextParameters + ['filters' => $filters, 'with_media' => $withMedia];

        foreach (self::FILE_PATH_KEYS as $filePathKey) {
            if (isset($rawParameters[$filePathKey])) {
                $rawParameters[$filePathKey] = $this->buildFilePath($rawParameters[$filePathKey], $contextParameters);
            }
        }

        if ($withGridContext) {
            $gridName = (null !== $request->get('gridName')) ? $request->get('gridName') : 'product_grid';
            if (isset($request->get($gridName)['_parameters'])) {
                $columns = explode(',', $request->get($gridName)['_parameters']['view']['columns']);
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

        if ($withLabels) {
            $dynamicConfiguration = array_merge(
                $dynamicConfiguration,
                [
                    'with_label' => true,
                    'header_with_label' => true,
                    'file_locale' => $fileLocale
                ]
            );
        }

        $configuration = array_merge($rawParameters, $dynamicConfiguration);
        $configuration['user_to_notify'] = $this->getUser()->getUsername();

        $jobExecution = $this->jobLauncher->launch($jobInstance, $this->getUser(), $configuration);

        return new JsonResponse(['job_id' => $jobExecution->getId()]);
    }

    /**
     * Get a user from the Security Context
     *
     * @see \Symfony\Component\Security\Core\Authentication\Token\TokenInterface::getUser()
     */
    protected function getUser(): ?UserInterface
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
     * @throws \LogicException If datasource is not a ProductDatasource or a ProductAndProductModelDatasource
     *
     * @return string[] Returns [] || ['locale' => 'en_US', 'scope' => 'mobile']
     */
    protected function getContextParameters(Request $request): array
    {
        $datagridName = $request->get('gridName');
        $datagrid = $this->datagridManager->getDatagrid($datagridName);
        $dataSource = $datagrid->getDatasource();

        if (!$dataSource instanceof ProductDatasource && !$dataSource instanceof ProductAndProductModelDatasource) {
            throw new \LogicException('getContextParameters is only implemented for ProductDatasource and ProductAndProductModelDatasource');
        }

        $user = $this->getUser();
        $dataSourceParams = $dataSource->getParameters();
        $contextParams = [];
        if (is_array($dataSourceParams)) {
            $contextParams = [
                'locale'    => $dataSourceParams['dataLocale'],
                'scope'     => $dataSourceParams['scopeCode'],
                'ui_locale' => null !== $user ?
                    $user->getUiLocale()->getCode() :
                    $this->requestStack->getCurrentRequest()->getDefaultLocale()
            ];
        }

        return $contextParams;
    }

    /**
     * Build file path to replace pattern like %locale%, %scope% by real data
     */
    protected function buildFilePath(string $filePath, array $contextParameters): string
    {
        $data = ['%datetime%' => date(static::DATETIME_FORMAT)];
        foreach ($contextParameters as $key => $value) {
            $data['%' . $key . '%'] = $value;
        }

        return strtr($filePath, $data);
    }
}
