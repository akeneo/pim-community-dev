<?php

namespace Pim\Bundle\DataGridBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Pim\Bundle\DataGridBundle\Entity\DatagridView;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\EnrichBundle\Exception\DeleteException;
use Pim\Bundle\DataGridBundle\Manager\DatagridViewManager;

/**
 * Datagrid view controller
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatagridViewController extends AbstractDoctrineController
{
    /** @var DatagridViewManager */
    protected $datagridViewManager;

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
     * @param DatagridViewManager      $datagridViewManager
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
        DatagridViewManager $datagridViewManager
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

        $this->datagridViewManager = $datagridViewManager;
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
        $user         = $this->getUser();
        $repository   = $this->getRepository('PimDataGridBundle:DatagridView');
        $activeViewId = $request->get('gridView', null);

        $activeView = $activeViewId ? $repository->find($activeViewId) : null;
        if (!$activeView) {
            $activeView = $this->datagridViewManager->getDefaultDatagridView($alias, $user);
        }

        $datagridView = new DatagridView();
        $datagridView->setOwner($user);
        $datagridView->setDatagridAlias($alias);
        $datagridView->setColumns($activeView->getColumns());

        $form = $this->createForm('pim_datagrid_view', $datagridView);

        if ($request->isMethod('POST')) {
            $form->submit($request);
            $violations = $this->validator->validate($datagridView, ['Default', 'Creation']);
            if ($violations->count()) {
                foreach ($violations as $violation) {
                    $this->addFlash('error', $violation->getMessage());
                }
            } else {
                $this->persist($datagridView);
            }

            return $this->redirectToRoute(
                'pim_enrich_product_index',
                [
                    'dataLocale' => $request->get('dataLocale'),
                    'gridView'   => $datagridView->getId()
                ]
            );
        }

        $views = $repository->findAllForUser($alias, $user);

        return $this->render(
            'PimDataGridBundle:Datagrid:_views.html.twig',
            [
                'alias'      => $alias,
                'views'      => $views,
                'activeView' => $activeView,
                'form'       => $form->createView(),
                'dataLocale' => $request->get('dataLocale')
            ]
        );
    }

    /**
     * Change to a different datagrid view
     * Makes sure that the view is in a clean state
     *
     * @param Request      $request
     * @param string       $alias
     * @param DatagridView $view
     *
     * @return Response
     */
    public function changeViewAction(Request $request, $alias, DatagridView $view)
    {
        if ($view->getOwner() === $this->getUser()) {
            $view->setConfiguredColumns($view->getColumns());
            $this->persist($view);
        }

        return $this->redirectToRoute(
            'pim_enrich_product_index',
            [
                'dataLocale' => $request->get('dataLocale'),
                'gridView'   => $view->getId()
            ]
        );
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
        $view    = $this->datagridViewManager->getEditableDatagridView($view);
        $columns = $this->datagridViewManager->getColumnChoices($alias);

        $form = $this->createForm(
            'pim_datagrid_view_configuration',
            $view,
            [
                'columns' => $this->sortArrayByArray($columns, $view->getColumns()),
                'action'  => $this->generateUrl(
                    'pim_datagrid_view_configure',
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
                $this->persist($view);
            }

            return $this->redirectToRoute(
                'pim_enrich_product_index',
                [
                    'dataLocale' => $request->get('dataLocale'),
                    'gridView'   => $view->getId()
                ]
            );
        }

        return $this->render('PimDataGridBundle:Datagrid:edit.html.twig', ['form' => $form->createView()]);
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
            return $this->redirectToRoute('pim_enrich_product_index', ['dataLocale' => $request->get('dataLocale')]);
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
     * Persist a view
     *
     * @param object $entity
     */
    protected function persist($entity)
    {
        $em = $this->getManager();
        $em->persist($entity);
        $em->flush();
    }
}
