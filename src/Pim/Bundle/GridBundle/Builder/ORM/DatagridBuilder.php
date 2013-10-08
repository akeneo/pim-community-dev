<?php

namespace Pim\Bundle\GridBundle\Builder\ORM;

use Pim\Bundle\GridBundle\Action\Export\ExportActionInterface;

use Oro\Bundle\GridBundle\Action\ActionFactoryInterface;
use Oro\Bundle\GridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\GridBundle\Datagrid\ParametersInterface;
use Oro\Bundle\GridBundle\Datagrid\ProxyQueryInterface;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Filter\FilterFactoryInterface;
use Oro\Bundle\GridBundle\Route\RouteGeneratorInterface;
use Oro\Bundle\GridBundle\Sorter\SorterFactoryInterface;
use Oro\Bundle\GridBundle\Builder\ORM\DatagridBuilder as OroDatagridBuilder;
use Oro\Bundle\SecurityBundle\SecurityFacade;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Serializer\Serializer;

/**
 * Override of OroPlatform datagrid builder
 * Inject serializer in datagrid builder allowing export
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatagridBuilder extends OroDatagridBuilder
{
    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * Constructor
     *
     * @param FormFactoryInterface     $formFactory
     * @param EventDispatcherInterface $eventDispatcher
     * @param SecurityFacade           $securityFacade
     * @param FilterFactoryInterface   $filterFactory
     * @param SorterFactoryInterface   $sorterFactory
     * @param ActionFactoryInterface   $actionFactory
     * @param string                   $className
     * @param Serializer               $serializer
     */
    public function __construct(
        FormFactoryInterface $formFactory,
        EventDispatcherInterface $eventDispatcher,
        SecurityFacade $securityFacade,
        FilterFactoryInterface $filterFactory,
        SorterFactoryInterface $sorterFactory,
        ActionFactoryInterface $actionFactory,
        $className,
        Serializer $serializer
    ) {
        parent::__construct(
            $formFactory,
            $eventDispatcher,
            $securityFacade,
            $filterFactory,
            $sorterFactory,
            $actionFactory,
            $className
        );

        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     *
     * Add the serializer to the datagrid object
     */
    public function getBaseDatagrid(
        ProxyQueryInterface $proxyQuery,
        FieldDescriptionCollection $fieldCollection,
        RouteGeneratorInterface $routeGenerator,
        ParametersInterface $parameters,
        $name
    ) {
        $datagrid = parent::getBaseDatagrid($proxyQuery, $fieldCollection, $routeGenerator, $parameters, $name);

        $datagrid->setSerializer($this->serializer);

        return $datagrid;
    }

    /**
     * Add export action
     *
     * @param DatagridInterface     $datagrid
     * @param ExportActionInterface $exportAction
     */
    public function addExportAction(DatagridInterface $datagrid, ExportActionInterface $exportAction)
    {
        $aclResource = $exportAction->getAclResource();
        if (!$aclResource || $this->securityFacade->isGranted($aclResource)) {
            $datagrid->addExportAction($exportAction);
        }
    }
}
