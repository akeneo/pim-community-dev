<?php

namespace Pim\Bundle\DashboardBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Pim\Bundle\CatalogBundle\AbstractController\AbstractController;
use Pim\Bundle\DashboardBundle\Widget\Registry;

/**
 * Widget controller
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WidgetController extends AbstractController
{
    /** @var Registry */
    protected $widgetRegistry;

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
     * @param Registry                 $registry
     */
    public function __construct(
        Request $request,
        EngineInterface $templating,
        RouterInterface $router,
        SecurityContextInterface $securityContext,
        FormFactoryInterface $formFactory,
        ValidatorInterface $validator,
        TranslatorInterface $translator,
        Registry $widgetRegistry
    ) {
        parent::__construct($request, $templating, $router, $securityContext, $formFactory, $validator, $translator);

        $this->widgetRegistry = $widgetRegistry;
    }

    public function showAction($alias)
    {
        if (null === $widget = $this->widgetRegistry->get($alias)) {
            return $this->render('PimDashboardBundle:Widget:error.html.twig', array('alias' => $alias));
        }

        return $this->render($widget->getTemplate(), array('widget' => $widget->getParameters()));
    }
}
