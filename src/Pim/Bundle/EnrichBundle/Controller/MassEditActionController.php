<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Akeneo\Bundle\BatchBundle\Job\DoctrineJobRepository;
use Akeneo\Bundle\BatchBundle\Launcher\SimpleJobLauncher;
use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;
use Pim\Bundle\DataGridBundle\Adapter\GridFilterAdapterInterface;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\EnrichBundle\Factory\MassEditJobConfigurationFactory;
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

    /** @var SimpleJobLauncher */
    protected $simpleJobLauncher;

    /** @var DoctrineJobRepository */
    protected $jobRepository;

    /** @var ConnectorRegistry */
    protected $connectorRegistry;

    /** @var MassEditFormResolver */
    protected $massEditFormResolver;

    /** @var SaverInterface */
    protected $jobConfigSaver;

    /** @var MassEditJobConfigurationFactory */
    protected $jobConfigFactory;

    /**
     * Constructor
     *
     * @param Request                         $request
     * @param EngineInterface                 $templating
     * @param RouterInterface                 $router
     * @param SecurityContextInterface        $securityContext
     * @param FormFactoryInterface            $formFactory
     * @param ValidatorInterface              $validator
     * @param TranslatorInterface             $translator
     * @param EventDispatcherInterface        $eventDispatcher
     * @param ManagerRegistry                 $doctrine
     * @param MassActionParametersParser      $parametersParser
     * @param GridFilterAdapterInterface      $gridFilterAdapter
     * @param SimpleJobLauncher               $simpleJobLauncher
     * @param DoctrineJobRepository           $jobRepository
     * @param ConnectorRegistry               $connectorRegistry
     * @param OperationRegistryInterface      $operationRegistry
     * @param MassEditFormResolver            $massEditFormResolver
     * @param MassEditJobConfigurationFactory $jobConfigFactory
     * @param SaverInterface                  $jobConfigSaver
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
        SimpleJobLauncher $simpleJobLauncher,
        DoctrineJobRepository $jobRepository,
        ConnectorRegistry $connectorRegistry,
        OperationRegistryInterface $operationRegistry,
        MassEditFormResolver $massEditFormResolver,
        MassEditJobConfigurationFactory $jobConfigFactory,
        SaverInterface $jobConfigSaver
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
        $this->simpleJobLauncher    = $simpleJobLauncher;
        $this->jobRepository        = $jobRepository;
        $this->connectorRegistry    = $connectorRegistry;
        $this->operationRegistry    = $operationRegistry;
        $this->massEditFormResolver = $massEditFormResolver;
        $this->jobConfigSaver       = $jobConfigSaver;
        $this->jobConfigFactory     = $jobConfigFactory;
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

            $jobCode = $operation->getBatchJobCode();
            $jobInstance = $this->getRepository('AkeneoBatchBundle:JobInstance')->findOneBy(['code' => $jobCode]);

            if (null === $jobInstance) {
                throw new NotFoundResourceException(sprintf('No job found with job code "%s"', $jobCode));
            }

            $operation->finalize();

            $rawConfiguration = $operation->getBatchConfig();
            $jobExecution = new JobExecution();
            $jobExecution->setJobInstance($jobInstance)->setUser($this->getUser());
            $this->persist($jobExecution, true);

            $massEditConf = $this->jobConfigFactory->create($jobExecution, $rawConfiguration);
            $this->jobConfigSaver->save($massEditConf);

            $this->simpleJobLauncher->launch($jobInstance, $this->getUser(), $rawConfiguration, $jobExecution);
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
