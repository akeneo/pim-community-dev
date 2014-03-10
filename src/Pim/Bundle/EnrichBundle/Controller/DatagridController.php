<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

use Doctrine\ORM\QueryBuilder;

use Oro\Bundle\DataGridBundle\Datagrid\Manager as DatagridManager;
use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;
use Oro\Bundle\UserBundle\Entity\User;

use Pim\Bundle\EnrichBundle\Entity\DatagridConfiguration;
use Pim\Bundle\EnrichBundle\Entity\DatagridView;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\EnrichBundle\Exception\DeleteException;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ContextConfigurator;
use Pim\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher;
use Pim\Bundle\UserBundle\Context\UserContext;

/**
 * Datagrid configuration controller
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatagridController extends AbstractDoctrineController
{
    /** @var DatagridManager $manager */
    protected $manager;

    /** @var MassActionParametersParser $parametersParser */
    protected $parametersParser;

    /** @var MassActionDispatcher $massActionDispatcher */
    protected $massActionDispatcher;

    /** @var SerializerInterface $serializer */
    protected $serializer;

    /** @var ProductManager $productManager */
    protected $productManager;

    /** @var UserContext $userContext */
    protected $userContext;

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
     * @param RegistryInterface          $doctrine
     * @param DatagridManager            $manager
     * @param MassActionParametersParset $parametersParser
     * @param MassActionDispatcher       $massActionDispatcher
     * @param SerializerInterface        $serializer
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        RegistryInterface $doctrine,
        DatagridManager $manager,
        MassActionParametersParser $parametersParser,
        MassActionDispatcher $massActionDispatcher,
        SerializerInterface $serializer,
        ProductManager $productManager,
        UserContext $userContext
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $securityContext,
            $formFactory,
            $validator,
            $translator,
            $doctrine
        );

        $this->manager = $manager;

        $this->productManager = $productManager;
        $this->userContext          = $userContext;
        $this->parametersParser     = $parametersParser;
        $this->massActionDispatcher = $massActionDispatcher;
        $this->serializer           = $serializer;

        $this->productManager->setLocale($this->getDataLocale());
    }

    /**
     * Display or save datagrid configuration
     *
     * @param Request $request
     * @param string  $alias
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, $alias)
    {
        $user    = $this->getUser();
        $columns = $this->getColumnChoices($alias);

        if (null === $configuration = $this->getDatagridConfiguration($alias, $user)) {
            $configuration = new DatagridConfiguration();
            $configuration->setUser($user);
            $configuration->setDatagridAlias($alias);
            $configuration->setColumns(array_keys($columns));
        }

        $form = $this->createForm(
            'pim_enrich_datagrid_configuration',
            $configuration,
            [
                'columns' => $this->sortArrayByArray($columns, $configuration->getColumns()),
                'action'  => $this->generateUrl(
                    'pim_enrich_datagrid_edit',
                    [
                        'alias'      => $alias,
                        'dataLocale' => $request->get('dataLocale')
                    ]
                ),
            ]
        );

        if ($request->isMethod('POST')) {
            $form->submit($request);
            $violations = $this->validator->validate($configuration);
            if ($violations->count()) {
                foreach ($violations as $violation) {
                    $this->addFlash('error', $violation->getMessage());
                }
            } else {
                $em = $this->getManager();
                $em->persist($configuration);
                $em->flush();
            }

            return $this->redirectToRoute('pim_enrich_product_index', ['dataLocale' => $request->get('dataLocale')]);
        }

        return $this->render('PimEnrichBundle:Datagrid:edit.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Display or save datagrid views
     *
     * @param Request $request
     * @param string  $alias
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewsAction(Request $request, $alias)
    {
        $user       = $this->getUser();
        $repository = $this->getRepository('PimEnrichBundle:DatagridView');
        $baseViewId = $request->get('gridView', null);

        $baseView = $baseViewId ? $repository->find($baseViewId) : null;
        if ($baseView) {
            $columns = $baseView->getColumns();
        } else {
            $configuration = $this->getDatagridConfiguration($alias, $user);
            $columns       = $configuration ? $configuration->getColumns() : array_keys($this->getColumnChoices($alias));
        }

        $datagridView = new DatagridView();
        $datagridView->setOwner($user);
        $datagridView->setDatagridAlias($alias);
        $datagridView->setColumns($columns);

        $form = $this->createForm('pim_enrich_datagrid_view', $datagridView);

        if ($request->isMethod('POST')) {
            $form->submit($request);
            $violations = $this->validator->validate($datagridView);
            if ($violations->count()) {
                foreach ($violations as $violation) {
                    $this->addFlash('error', $violation->getMessage());
                }
            } else {
                $em = $this->getManager();
                $em->persist($datagridView);
                $em->flush();
            }

            return $this->redirectToRoute(
                'pim_enrich_product_index',
                [
                    'dataLocale' => $request->get('dataLocale'),
                    'gridView'   => $datagridView->getId()
                ]
            );
        }

        $views = $repository->findBy(['datagridAlias' => $alias]);

        return $this->render(
            'PimEnrichBundle:Datagrid:_views.html.twig',
            [
                'alias'      => $alias,
                'views'      => $views,
                'columns'    => $columns,
                'form'       => $form->createView(),
                'dataLocale' => $request->get('dataLocale'),
                'gridView'   => $request->get('gridView', null)
            ]
        );
    }

    /**
     * Remove a datagrid view
     *
     * @param Request      $request
     * @param DatagridView $view
     *
     * @throws DeleteException If the current user doesn't own the view
     *
     * @return Response|RedirectResponse
     */
    public function removeViewAction(Request $request, DatagridView $view)
    {
        if ($view->getOwner() !== $this->getUser()) {
            throw new DeleteException($this->getTranslator()->trans('flash.datagrid view.not removable'));
        }

        $em = $this->getManager();
        $em->remove($view);
        $em->flush();

        if ($request->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToRoute(
                'pim_enrich_product_index',
                [
                    'dataLocale' => $request->get('dataLocale')
                ]
            );
        }
    }

    /**
     * Call export action
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function exportAction(Request $request)
    {
        // Export time execution depends on entities exported
        ignore_user_abort(false);
        set_time_limit(0);

        $parameters  = $this->parametersParser->parse($request);
        $requestData = array_merge($request->query->all(), $request->request->all());

        $qb = $this->massActionDispatcher->dispatch(
            $requestData['gridName'],
            $requestData['actionName'],
            $parameters,
            $requestData
        );

        $dateTime = new \DateTime();
        $fileName = sprintf(
            'products_export_%s_%s_%s.csv',
            $this->getDataLocale(),
            $this->productManager->getScope(),
            $dateTime->format('Y-m-d_H:i:s')
        );

        // prepare response
        $response = new StreamedResponse();
        $attachment = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $fileName);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', $attachment);
        $response->setCallback($this->quickExportCallback($qb));

        return $response->send();
    }

    /**
     * Quick export callback
     *
     * @param QueryBuilder $qb
     *
     * @return \Closure
     */
    protected function quickExportCallback(QueryBuilder $qb)
    {
        return function () use ($qb) {
            flush();

            $format  = 'csv';
            $context = [
                'withHeader'    => true,
                'heterogeneous' => true
            ];

            $rootAlias = $qb->getRootAlias();
            $qb->resetDQLPart('select');
            $qb->resetDQLPart('from');
            $qb->select($rootAlias);
            $qb->from($this->productManager->getFlexibleName(), $rootAlias);

            $results = $qb->getQuery()->execute();
            echo $this->serializer->serialize($results, $format, $context);

            flush();
        };
    }

    /**
     * Sort an array by key given an other array values
     *
     * @param array $array
     * @param array $orderArray
     *
     * @return array
     */
    protected function sortArrayByArray(array $array, array $orderArray)
    {
        $ordered = [];
        foreach ($orderArray as $key) {
            if (array_key_exists($key, $array)) {
                $ordered[$key] = $array[$key];
                unset($array[$key]);
            }
        }

        return $ordered + $array;
    }

    /**
     * Get datagrid columns as choices
     *
     * @param string $alias
     *
     * @return array
     */
    protected function getColumnChoices($alias)
    {
        $choices = array();

        $columnsConfig = $this
            ->manager
            ->getDatagrid($alias)
            ->getAcceptor()
            ->getConfig()
            ->offsetGetByPath(sprintf(ContextConfigurator::SOURCE_PATH, ContextConfigurator::AVAILABLE_COLUMNS_KEY));

        if ($columnsConfig) {
            foreach ($columnsConfig as $code => $meta) {
                $choices[$code] = $meta['label'];
            }
        }

        return $choices;
    }

    /**
     * Retrieve datagrid configuration from datagrid alias and user
     *
     * @param string $alias
     * @param User   $user
     *
     * @return null|DatagridConfiguration
     */
    protected function getDatagridConfiguration($alias, User $user)
    {
        return $this
            ->getRepository('PimEnrichBundle:DatagridConfiguration')
            ->findOneBy(
                [
                    'datagridAlias' => $alias,
                    'user'          => $user,
                ]
            );
    }

    /**
     * Get data locale code
     *
     * @throws \Exception
     *
     * @return string
     */
    protected function getDataLocale()
    {
        return $this->userContext->getCurrentLocaleCode();
    }
}
