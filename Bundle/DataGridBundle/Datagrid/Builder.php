<?php

namespace Oro\Bundle\DataGridBundle\Datagrid;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Extension\Acceptor;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Extension\ExtensionVisitorInterface;

class Builder
{
    const DATASOURCE_PATH          = '[source]';
    const DATASOURCE_TYPE_PATH     = '[source][type]';
    const DATASOURCE_ACL_PATH      = '[source][acl_resource]';
    const BASE_DATAGRID_CLASS_PATH = '[options][base_datagrid_class]';

    /** @var string */
    protected $baseDatagridClass;

    /** @var string */
    protected $acceptorClass;

    /** @var EventDispatcher */
    protected $eventDispatcher;

    /** @var DatasourceInterface[] */
    protected $dataSources = [];

    /** @var ExtensionVisitorInterface[] */
    protected $extensions = [];

    /** @var PropertyAccess */
    protected $accessor;

    /** @var SecurityFacade */
    protected $securityFacade;

    public function __construct(
        $baseDatagridClass,
        $acceptorClass,
        EventDispatcher $eventDispatcher,
        SecurityFacade $securityFacade
    ) {
        $this->baseDatagridClass = $baseDatagridClass;
        $this->acceptorClass     = $acceptorClass;
        $this->eventDispatcher   = $eventDispatcher;
        $this->securityFacade    = $securityFacade;

        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * Create, configure and build datagrid
     *
     * @param string $name
     * @param array  $config
     *
     * @return DatagridInterface
     */
    public function build($name, array $config)
    {
        $class = $this->getBaseDatagridClass($config);

        /** @var Acceptor $acceptor */
        $acceptor = new $this->acceptorClass($config);
        /** @var DatagridInterface $datagrid */
        $datagrid = new $class($name, $acceptor);

        $event = new BuildBefore($datagrid, $config);
        $this->eventDispatcher->dispatch(BuildBefore::NAME, $event);
        // duplicate event dispatch with grid name
        $this->eventDispatcher->dispatch(BuildBefore::NAME . '.' . $name, $event);

        // replace to config from event in case external changes
        $config = $event->getConfig();
        $acceptor->setConfig($config);

        $this->buildDataSource($datagrid, $config);

        foreach ($this->extensions as $extension) {
            if ($extension->isApplicable($config)) {
                $datagrid->addExtension($extension);
            }
        }

        $event = new BuildAfter($datagrid);
        $this->eventDispatcher->dispatch(BuildAfter::NAME, $event);
        // duplicate event dispatch with grid name
        $this->eventDispatcher->dispatch(BuildAfter::NAME . '.' . $name, $event);

        return $datagrid;
    }

    /**
     * Add datasource type
     * Automatically added services tagged by oro_grid.datasource tag
     *
     * @param string              $type
     * @param DatasourceInterface $dataSource
     *
     * @return $this
     */
    public function addDatasource($type, DatasourceInterface $dataSource)
    {
        $this->dataSources[$type] = $dataSource;

        return $this;
    }

    /**
     * Add extension
     * Automatically added services tagged by oro_grid.extension tag
     *
     * @param ExtensionVisitorInterface $extension
     *
     * @return $this
     */
    public function addExtension(ExtensionVisitorInterface $extension)
    {
        $this->extensions[] = $extension;

        return $this;
    }

    /**
     * Try to find datasource adapter and process it
     * Datasource object should be self-acceptable to grid
     *
     * @param DatagridInterface $grid
     * @param array             $config
     *
     * @throws \RuntimeException
     */
    protected function buildDataSource(DatagridInterface $grid, array $config)
    {
        if (!$sourceType = $this->accessor->getValue($config, self::DATASOURCE_TYPE_PATH)) {
            throw new \RuntimeException('Datagrid source does not configured');
        }

        if (!isset($this->dataSources[$sourceType])) {
            throw new \RuntimeException(sprintf('Datagrid source "%s" does not exist', $sourceType));
        }

        $acl = $this->accessor->getValue($config, self::DATASOURCE_ACL_PATH);
        if ($acl && !$this->isResourceGranted($acl)) {
            throw new AccessDeniedException('Access denied.');
        }

        $this->dataSources[$sourceType]->process($grid, $this->accessor->getValue($config, self::DATASOURCE_PATH));
    }

    /**
     * Check whenever need custom datagrid class
     *
     * @param array $config
     *
     * @return string
     */
    protected function getBaseDatagridClass(array $config)
    {
        return $this->accessor->getValue($config, self::BASE_DATAGRID_CLASS_PATH) ? : $this->baseDatagridClass;
    }

    /**
     * Checks if an access to a resource is granted or not
     *
     * @param string $aclResource An ACL annotation id or "permission;descriptor"
     *
     * @return bool
     */
    protected function isResourceGranted($aclResource)
    {
        $delimiter = strpos($aclResource, ';');
        if ($delimiter) {
            $permission = substr($aclResource, 0, $delimiter);
            $descriptor = substr($aclResource, $delimiter + 1);

            return $this->securityFacade->isGranted($permission, $descriptor);
        }

        return $this->securityFacade->isGranted($aclResource);
    }
}
