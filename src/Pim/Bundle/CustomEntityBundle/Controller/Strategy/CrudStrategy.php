<?php

namespace Pim\Bundle\CustomEntityBundle\Controller\Strategy;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Pim\Bundle\CustomEntityBundle\Configuration\ConfigurationInterface;

/**
 * Base strategy for custom entity controllers
 * 
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CrudStrategy implements StrategyInterface
{
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * Constructor
     *
     * @param FormFactoryInterface $formFactory
     * @param EngineInterface      $templating
     * @param RouterInterface      $router
     * @param TranslatorInterface  $translator
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        EngineInterface $templating,
        RouterInterface $router,
        TranslatorInterface $translator
    ) {
        $this->formFactory = $formFactory;
        $this->templating = $templating;
        $this->router = $router;
        $this->translator = $translator;
    }

    /**
     * Quick create action
     *
     * @param  ConfigurationInterface $configuration
     * @param  Request                $request
     * @return Response
     */
    public function createAction(ConfigurationInterface $configuration, Request $request)
    {
        $entity = $this->createEntity($configuration, $request);

        $form = $this->formFactory->create(
            $configuration->getCreateFormType(),
            $entity,
            $configuration->getCreateFormOptions()
        );
        $form->setData($entity);

        if ($request->getMethod() === 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $configuration->getManager()->save($entity);

                $this->addFlash($request, 'success', sprintf('flash.%s.created', $configuration->getName()));

                $response = array(
                    'status' => 1,
                    'url' => $this->router->generate(
                        $configuration->getCreateRedirectRoute($entity),
                        $configuration->getCreateRedirectRouteParameters($entity)
                    )
                );

                return new Response(json_encode($response));
            }
        }

        return $this->render(
            $configuration,
            $request,
            $configuration->getCreateTemplate(),
            array('form' => $form->createView())
        );
    }

    /**
     * Edit action
     *
     * @param  ConfigurationInterface $configuration
     * @param  Request                $request
     * @throws NotFoundHttpException
     * @return Response
     */
    public function editAction(ConfigurationInterface $configuration, Request $request)
    {
        $entity = $this->findEntity($configuration, $request);
        $form = $this->formFactory->create(
            $configuration->getEditFormType(),
            $entity,
            $configuration->getEditFormOptions()
        );

        if ($request->getMethod() === 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $configuration->getManager()->save($entity);
                $this->addFlash($request, 'success', sprintf('flash.%s.updated', $configuration->getName()));

                return new RedirectResponse(
                    $this->router->generate(
                        $configuration->getEditRedirectRoute($entity),
                        $configuration->getEditRedirectRouteParameters($entity)
                    )
                );
            }
        }

        return $this->render(
            $configuration,
            $request,
            $configuration->getEditTemplate(),
            array('form' => $form->createView())
        );
    }

    /**
     * Remove action
     *
     * @param  ConfigurationInterface $configuration
     * @param  Request                $request
     * @throws NotFoundHttpException
     * @return Response
     */
    public function removeAction(ConfigurationInterface $configuration, Request $request)
    {
        $entity = $this->findEntity($configuration, $request);
        $configuration->getManager()->remove($entity);

        return new Response('', 204);
    }

    /**
     * Returns the entity of the request
     *
     * @param  ConfigurationInterface $configuration
     * @param  Request                $request
     * @throws NotFoundHttpException
     * @return object
     */
    protected function findEntity(ConfigurationInterface $configuration, Request $request)
    {
        $entity = $configuration->getManager()->find(
            $configuration->getEntityClass(),
            $request->attributes->get('id'),
            $configuration->getFindOptions()
        );

        if (!$entity) {
            throw new NotFoundHttpException();
        }

        return $entity;
    }

    /**
     * Creates an entity
     *
     * @param  ConfigurationInterface $configuration
     * @param  Request                $request
     * @return object
     */
    protected function createEntity(ConfigurationInterface $configuration, Request $request)
    {
        return $configuration->getManager()->create(
            $configuration->getEntityClass(),
            $configuration->getCreateDefaultProperties(),
            $configuration->getCreateOptions()
        );
    }

    /**
     * Returns the default view vars
     *
     * @param  ConfigurationInterface $configuration
     * @param  Request                $request
     * @return array
     */
    protected function getViewVars(ConfigurationInterface $configuration, Request $request)
    {
        return array(
            'customEntityName' => $configuration->getName(),
            'baseTemplate'     => $configuration->getBaseTemplate(),
            'indexRoute'       => $configuration->getIndexRoute(),
            'editRoute'        => $configuration->getEditRoute(),
            'createRoute'      => $configuration->getCreateRoute(),
            'removeRoute'      => $configuration->getRemoveRoute()
        );
    }

    /**
     * Renders a template and returns a Response object
     *
     * @param  ConfigurationInterface $configuration
     * @param  Request                $request
     * @param  string                 $template
     * @param  array                  $parameters
     * @return Response
     */
    protected function render(
        ConfigurationInterface $configuration,
        Request $request,
        $template,
        array $parameters = array()
    ) {
        return $this->templating->renderResponse($template, $parameters + $this->getViewVars($configuration, $request));
    }

    /**
     * Adds a flash message
     *
     * @param Request $request
     * @param type    $type
     * @param type    $message
     */
    protected function addFlash(Request $request, $type, $message)
    {
        $request->getSession()->getFlashBag()
            ->add($type, $this->translator->trans($message));
    }
}
