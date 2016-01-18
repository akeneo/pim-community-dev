<?php

namespace Pim\Bundle\DataGridBundle\Controller;

use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\DataGridBundle\Entity\DatagridView;
use Pim\Bundle\DataGridBundle\Manager\DatagridViewManager;
use Pim\Bundle\EnrichBundle\AbstractController\AbstractDoctrineController;
use Pim\Bundle\EnrichBundle\Exception\DeleteException;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
     * @param TokenStorageInterface    $tokenStorage
     * @param FormFactoryInterface     $formFactory
     * @param ValidatorInterface       $validator
     * @param TranslatorInterface      $translator
     * @param EventDispatcherInterface $eventDispatcher
     * @param ManagerRegistry          $doctrine
     * @param DatagridViewManager      $datagridViewManager
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        TokenStorageInterface $tokenStorage,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $doctrine,
        DatagridViewManager $datagridViewManager
    ) {
        parent::__construct(
            $request,
            $templating,
            $router,
            $tokenStorage,
            $formFactory,
            $validator,
            $translator,
            $eventDispatcher,
            $doctrine
        );

        $this->datagridViewManager = $datagridViewManager;
    }

    /**
     * Display or save datagrid views
     *
     * @param Request           $request
     * @param string            $alias
     * @param DatagridView|null $view
     *
     * @return Response|JsonResponse
     */
    public function indexAction(Request $request, $alias, DatagridView $view = null)
    {
        $user = $this->getUser();

        if (!$view || $view->getOwner() !== $user) {
            $view = new DatagridView();
            $view->setOwner($user);
            $view->setDatagridAlias($alias);
        }

        $form = $this->createForm('pim_datagrid_view', $view);

        if ($request->isMethod('POST')) {
            $creation = !(bool) $view->getId();
            if (!$creation) {
                $form->remove('label');
            }
            $form->submit($request);

            // If the view was created based on the default view, set the default columns
            if (!$view->getColumns()) {
                $view->setColumns(array_keys($this->datagridViewManager->getColumnChoices($alias, true)));
            }

            $violations = $this->validator->validate($view);
            if ($violations->count()) {
                $messages = [];
                foreach ($violations as $violation) {
                    $messages[] = $this->getTranslator()->trans($violation->getMessage());
                }

                return new JsonResponse(['errors' => $messages]);
            } else {
                $this->datagridViewManager->save($view);

                if ($creation) {
                    $this->addFlash('success', 'flash.datagrid view.created');
                }

                return new JsonResponse(['id' => $view->getId()]);
            }
        }

        $views = $this->datagridViewManager->findPublic($alias);

        return $this->render(
            'PimDataGridBundle:Datagrid:_views.html.twig',
            [
                'alias' => $alias,
                'views' => $views,
                'form'  => $form->createView(),
            ]
        );
    }

    /**
     * List available datagrid columns
     *
     * @param string $alias
     *
     * @return JsonResponse
     */
    public function listColumnsAction($alias)
    {
        return new JsonResponse($this->datagridViewManager->getColumnChoices($alias));
    }

    /**
     * Remove a datagrid view
     *
     * @param Request      $request
     * @param DatagridView $view
     *
     * @throws DeleteException If the current user doesn't own the view
     *
     * @return Response
     */
    public function removeAction(Request $request, DatagridView $view)
    {
        if ($view->getOwner() !== $this->getUser()) {
            throw new DeleteException($this->getTranslator()->trans('flash.datagrid view.not removable'));
        }

        $this->datagridViewManager->remove($view);
        $this->addFlash('success', 'flash.datagrid view.removed');

        return new Response('', 204);
    }
}
