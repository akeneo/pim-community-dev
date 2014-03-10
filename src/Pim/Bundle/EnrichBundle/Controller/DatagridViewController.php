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
use Oro\Bundle\DataGridBundle\Datagrid\Manager as DatagridManager;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\EnrichBundle\Entity\DatagridView;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\EnrichBundle\Exception\DeleteException;
use Pim\Bundle\DataGridBundle\Datagrid\Product\ContextConfigurator;

/**
 * Datagrid view controller
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatagridViewController extends AbstractDoctrineController
{
    /** @var DatagridManager */
    protected $manager;

    /**
     * Constructor
     *
     * @param Request                  $request
     * @param EngineInterface          $templating
     * @param RouterInterface          $router
     * @param SecurityContextInterface $securityContext
     * @param FormFactoryInterface     $formFactory
     * @param ValidatorInterface       $validator
     * @param TranslatorInterface      $translator
     * @param RegistryInterface        $doctrine
     * @param DatagridManager          $manager
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
        DatagridManager $manager
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
    }

    /**
     * Display or configure datagrid view columns
     *
     * @param Request      $request
     * @param string       $alias
     * @param DatagridView $view
     *
     * @return Response
     */
    public function configureAction(Request $request, $alias, DatagridView $view)
    {
        $user    = $this->getUser();
        $columns = $this->getColumnChoices($alias);

        $form = $this->createForm(
            'pim_enrich_datagrid_view_configuration',
            $view,
            [
                'columns' => $this->sortArrayByArray($columns, $view->getColumns()),
                'action'  => $this->generateUrl(
                    'pim_enrich_datagrid_view_configure',
                    [
                        'alias'      => $alias,
                        'dataLocale' => $request->get('dataLocale'),
                        'id'         => $view->getId()
                    ]
                ),
            ]
        );

        if ($request->isMethod('POST')) {
            $form->submit($request);
            $violations = $this->validator->validate($view);
            if ($violations->count()) {
                foreach ($violations as $violation) {
                    $this->addFlash('error', $violation->getMessage());
                }
            } else {
                $em = $this->getManager();
                $em->persist($view);
                $em->flush();
            }

            return $this->redirectToRoute(
                'pim_enrich_product_index',
                [
                    'dataLocale' => $request->get('dataLocale'),
                    'gridView'   => $view->getId()
                ]
            );
        }

        return $this->render('PimEnrichBundle:Datagrid:edit.html.twig', ['form' => $form->createView()]);
    }

    /**
     * Display or save datagrid views
     *
     * @param Request $request
     * @param string  $alias
     *
     * @return Response
     */
    public function indexAction(Request $request, $alias)
    {
        $user       = $this->getUser();
        $repository = $this->getRepository('PimEnrichBundle:DatagridView');
        $baseViewId = $request->get('gridView', null);

        $baseView = $baseViewId ? $repository->find($baseViewId) : null;
        if ($baseView) {
            $columns = $baseView->getColumns();
        } else {
            $view    = $this->getDatagridView($alias, $user);
            $columns = $view ? $view->getColumns() : array_keys($this->getColumnChoices($alias));
        }

        $datagridView = new DatagridView();
        $datagridView->setOwner($user);
        $datagridView->setDatagridAlias($alias);
        $datagridView->setColumns($columns);

        $form = $this->createForm('pim_enrich_datagrid_view', $datagridView);

        if ($request->isMethod('POST')) {
            $form->submit($request);
            $violations = $this->validator->validate($datagridView, ['Default', 'Creation']);
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
    public function removeAction(Request $request, DatagridView $view)
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
     * Retrieve datagrid view from datagrid alias and user
     *
     * @param string $alias
     * @param User   $user
     *
     * @return DatagridView
     */
    protected function getDatagridView($alias, User $user)
    {
        $view = $this
            ->getRepository('PimEnrichBundle:DatagridView')
            ->findOneBy(
                [
                    'datagridAlias' => $alias,
                    'owner'         => $user,
                ]
            );

        if (!$view) {
            $view = new DatagridView();
            $view->setOwner($user);
            $view->setDatagridAlias($alias);
            $view->setColumns(array_keys($this->getColumnChoices($alias)));

            $em = $this->getManager();
            $em->persist($view);
            $em->flush();
        }

        return $view;
    }
}
