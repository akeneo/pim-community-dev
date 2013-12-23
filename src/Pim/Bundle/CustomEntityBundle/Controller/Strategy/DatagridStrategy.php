<?php

namespace Pim\Bundle\CustomEntityBundle\Controller\Strategy;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\CustomEntityBundle\Configuration\ConfigurationInterface;
use Pim\Bundle\GridBundle\Helper\DatagridHelperInterface;

/**
 * Worker for custom entities with datagrids
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatagridStrategy extends CrudStrategy
{
    /**
     * @var DatagridHelperInterface
     */
    protected $datagridHelper;

    /**
     * Constructor
     *
     * @param FormFactoryInterface    $formFactory
     * @param EngineInterface         $templating
     * @param RouterInterface         $router
     * @param TranslatorInterface     $translator
     * @param DatagridHelperInterface $datagridHelper
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        EngineInterface $templating,
        RouterInterface $router,
        TranslatorInterface $translator,
        DatagridHelperInterface $datagridHelper
    ) {
        parent::__construct($formFactory, $templating, $router, $translator);

        $this->datagridHelper = $datagridHelper;
    }

    /**
     * Index action
     *
     * @param ConfigurationInterface $configuration
     * @param Request                $request
     *
     * @return Response
     */
    public function indexAction(ConfigurationInterface $configuration, Request $request)
    {
        $datagrid = $this->datagridHelper->getDatagrid(
            $configuration->getName(),
            $this->createQueryBuilder($configuration, $request),
            $configuration->getDatagridNamespace()
        );
        $routeGenerator = $datagrid->getRouteGenerator();
        $routeGenerator->setRouteParameters(array('customEntityName' => $configuration->getName()));

        $template = ($request->getRequestFormat() === 'json')
            ? 'OroGridBundle:Datagrid:list.json.php' : $configuration->getIndexTemplate();

        return $this->render($configuration, $request, $template, array('datagrid' => $datagrid->createView()));
    }

    /**
     * Creates the query builder for the datagrid
     *
     * @param ConfigurationInterface $configuration
     * @param Request                $request
     *
     * @return QueryBuilder
     */
    protected function createQueryBuilder(ConfigurationInterface $configuration, Request $request)
    {
        return $configuration->getManager()->createQueryBuilder(
            $configuration->getEntityClass(),
            $configuration->getQueryBuilderOptions()
        );
    }
}
