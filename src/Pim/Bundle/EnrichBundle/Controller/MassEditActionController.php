<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Akeneo\Bundle\BatchBundle\Job\DoctrineJobRepository;
use Akeneo\Bundle\BatchBundle\Manager\JobLauncher;
use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;
use Pim\Bundle\DataGridBundle\Adapter\GridFilterAdapterInterface;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\EnrichBundle\MassEditAction\MassEditFormResolver;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\OperationRegistryInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Translation\Exception\NotFoundResourceException;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Mass edit operation controller
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MassEditActionController extends AbstractDoctrineController
{
    /** @var MassActionParametersParser */
    protected $parametersParser;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var GridFilterAdapterInterface */
    protected $gridFilterAdapter;

    /** @var JobLauncher */
    protected $jobLauncher;

    /** @var DoctrineJobRepository */
    protected $jobRepository;

    /** @var ConnectorRegistry */
    protected $connectorRegistry;

    /** @var MassEditFormResolver */
    protected $massEditFormResolver;

    /**
     * Constructor
     *
     * @param Request                    $request
     * @param EngineInterface            $templating
     * @param RouterInterface            $router
     * @param SecurityContextInterface   $securityContext
     * @param FormFactoryInterface       $formFactory
     * @param ValidatorInterface         $validator
     * @param TranslatorInterface        $translator
     * @param EventDispatcherInterface   $eventDispatcher
     * @param ManagerRegistry            $doctrine
     * @param MassActionParametersParser $parametersParser
     * @param GridFilterAdapterInterface $gridFilterAdapter
     * @param JobLauncher                $jobLauncher
     * @param DoctrineJobRepository      $jobRepository
     * @param ConnectorRegistry          $connectorRegistry
     * @param OperationRegistryInterface $operationRegistry
     * @param MassEditFormResolver       $massEditFormResolver
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $doctrine,
        MassActionParametersParser $parametersParser,
        GridFilterAdapterInterface $gridFilterAdapter,
        JobLauncher $jobLauncher,
        DoctrineJobRepository $jobRepository,
        ConnectorRegistry $connectorRegistry,
        OperationRegistryInterface $operationRegistry,
        MassEditFormResolver $massEditFormResolver
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $securityContext,
            $formFactory,
            $validator,
            $translator,
            $eventDispatcher,
            $doctrine
        );

        $this->parametersParser     = $parametersParser;
        $this->gridFilterAdapter    = $gridFilterAdapter;
        $this->jobLauncher          = $jobLauncher;
        $this->jobRepository        = $jobRepository;
        $this->connectorRegistry    = $connectorRegistry;
        $this->operationRegistry    = $operationRegistry;
        $this->massEditFormResolver = $massEditFormResolver;
    }

    /**
     * @Template
     * @AclAncestor("pim_enrich_mass_edit")
     *
     * @return array
     */
    public function chooseAction()
    {
        $gridName     = $this->request->get('gridName');
        $objectsCount = $this->request->get('objectsCount');
        $itemsName    = $this->getItemsName($gridName);

        $form = $this->massEditFormResolver->getAvailableOperationsForm($gridName);

        if ($this->request->isMethod('POST')) {
            $form->submit($this->request);
            if ($form->isValid()) {
                $data = $form->getData();

                return $this->redirectToRoute(
                    'pim_enrich_mass_edit_action_configure',
                    $this->getQueryParams() + ['operationAlias' => $data['operationAlias']]
                );
            }
        }

        return [
            'form'        => $form->createView(),
            'count'       => $objectsCount,
            'queryParams' => $this->getQueryParams(),
            'itemsName'   => $itemsName
        ];
    }

    /**
     * @param string $operationAlias
     *
     * @AclAncestor("pim_enrich_mass_edit")
     * @throws NotFoundHttpException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function configureAction($operationAlias)
    {
        $operation    = $this->operationRegistry->get($operationAlias);
        $itemsName    = $operation->getItemsName();
        $productCount = $this->request->get('objectsCount');

        $form = $this->massEditFormResolver->getConfigurationForm($operationAlias);

        if ($this->request->isMethod('POST')) {
            $form->remove('operationAlias');
            $form->submit($this->request);
            $form = $this->massEditFormResolver->getConfigurationForm($operationAlias, $form->getNormData());
            $operation = $form->getNormData();
        }

        return $this->render(
            sprintf('PimEnrichBundle:MassEditAction:configure/%s.html.twig', $operationAlias),
            [
                'form'           => $form->createView(),
                'operationAlias' => $operationAlias,
                'operation'      => $operation,
                'queryParams'    => $this->getQueryParams(),
                'productCount'   => $productCount,
                'itemsName'      => $itemsName,
            ]
        );
    }

    /**
     * @param string $operationAlias
     *
     * @AclAncestor("pim_enrich_mass_edit")
     * @throws NotFoundHttpException
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function performAction($operationAlias)
    {
        $operation    = $this->operationRegistry->get($operationAlias);
        $itemsName    = $operation->getItemsName();
        $productCount = $this->request->get('objectsCount');

        $form = $this->massEditFormResolver->getConfigurationForm($operationAlias);
        $form->remove('operationAlias');
        $form->submit($this->request);

        if ($form->isValid()) {
            $operation = $form->getData();
            $pimFilters = $this->gridFilterAdapter->adapt($this->request);
            $operation->setFilters($pimFilters);

            //TODO: to remove !
            $jobCode = $operation->getBatchJobCode();
            $jobInstance = $this->jobRepository->getJobManager()
                ->getRepository('AkeneoBatchBundle:JobInstance')
                ->findOneByCode($jobCode);

            if (null === $jobInstance) {
                throw new NotFoundResourceException(sprintf('No job found with job code "%s"', $jobCode));
            }

            $operation->finalize();
            $rawConfiguration = $operation->getBatchConfig();

            // TODO: Fixme, we should be able to remove this line without having an error
            $jobInstance = $this->getJobInstance($jobInstance->getId());

            $this->jobLauncher->launch($jobInstance, $this->getUser(), $rawConfiguration);
        }

        if ($form->isValid()) {
            $this->addFlash(
                'success',
                sprintf('pim_enrich.mass_edit_action.%s.launched_flash', $operationAlias)
            );

            return $this->redirectToRoute('pim_enrich_product_index');
        }

        return $this->render(
            sprintf('PimEnrichBundle:MassEditAction:configure/%s.html.twig', $operationAlias),
            [
                'form'           => $form->createView(),
                'operationAlias' => $operationAlias,
                'itemsName'      => $itemsName,
                'productCount'   => $productCount,
                'queryParams'    => $this->getQueryParams()
            ]
        );
    }

    /**
     * Get a job instance
     *
     * @param integer $id
     * @param boolean $checkStatus
     *
     * @return JobInstance
     *
     * @throws NotFoundHttpException
     */
    protected function getJobInstance($id, $checkStatus = true)
    {
        $jobInstance = $this->findOr404('AkeneoBatchBundle:JobInstance', $id);

        // Fixme: should look at the job execution to see the status of a job instance execution
        if ($checkStatus && JobInstance::STATUS_IN_PROGRESS === $jobInstance->getStatus()) {
            throw $this->createNotFoundException(
                sprintf('The %s "%s" is currently in progress', $jobInstance->getType(), $jobInstance->getLabel())
            );
        }

        $job = $this->connectorRegistry->getJob($jobInstance);

        if (null === $job) {
            throw $this->createNotFoundException(
                sprintf(
                    'The following %s does not exist anymore. Please check configuration:<br />' .
                    'Connector: %s<br />' .
                    'Type: %s<br />' .
                    'Alias: %s',
                    $this->getJobType(),
                    $jobInstance->getConnector(),
                    $jobInstance->getType(),
                    $jobInstance->getAlias()
                )
            );
        }
        $jobInstance->setJob($job);

        return $jobInstance;
    }

    /**
     * @param string $gridName
     *
     * @return string
     */
    protected function getItemsName($gridName)
    {
        switch ($gridName) {
            case 'product-grid':
                $itemsName = 'product';
                break;
            case 'family-grid':
                $itemsName = 'family';
                break;
            default:
                $itemsName = 'item';
                break;
        }

        return $itemsName;
    }

    /**
     * Get the datagrid query parameters
     *
     * @return array
     */
    protected function getQueryParams()
    {
        $params = $this->parametersParser->parse($this->request);

        $params['gridName']     = $this->request->get('gridName');
        $params['actionName']   = $this->request->get('actionName');
        $params['values']       = implode(',', $params['values']);
        $params['filters']      = json_encode($params['filters']);
        $params['dataLocale']   = $this->request->get('dataLocale', null);
        $params['objectsCount'] = $this->request->get('objectsCount');

        return $params;
    }
}
