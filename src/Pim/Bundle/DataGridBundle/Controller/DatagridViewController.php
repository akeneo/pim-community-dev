<?php

namespace Pim\Bundle\DataGridBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
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
            $form->submit($request);
            $violations = $this->validator->validate($view, $view->getId() ? ['Default'] : ['Default', 'Creation']);
            if ($violations->count()) {
                $messages = [];
                foreach ($violations as $violation) {
                    $messages[] = $this->getTranslator()->trans($violation->getMessage());
                }

                return new JsonResponse(['errors' => $messages]);
            } else {
                $em = $this->getManager();
                $em->persist($entity);
                $em->flush();

                return new JsonResponse(['id' => $view->getId()]);
            }
        }

        $views = $this->getRepository('PimDataGridBundle:DatagridView')->findAllForUser($alias, $user);

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
        $columns = $this->datagridViewManager->getColumnChoices($alias);

        return new JsonResponse($columns);
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
        if ($view->getOwner() !== $this->getUser() || $view->isDefault()) {
            throw new DeleteException($this->getTranslator()->trans('flash.datagrid view.not removable'));
        }

        $em = $this->getManager();
        $em->remove($view);
        $em->flush();

        $this->addFlash('success', 'flash.datagrid view.removed');

        return new Response('', 204);
    }
}
