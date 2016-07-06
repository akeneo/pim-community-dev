<?php

namespace Oro\Bundle\DataGridBundle\Datagrid;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Event\BuildAfter;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Extension\Acceptor;
use Oro\Bundle\DataGridBundle\Extension\ExtensionVisitorInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class Builder
{
    const DATASOURCE_PATH = '[source]';
    const DATASOURCE_TYPE_PATH = '[source][type]';
    const DATASOURCE_ACL_PATH = '[source][acl_resource]';
    const BASE_DATAGRID_CLASS_PATH = '[options][base_datagrid_class]';

    /** @var string */
    protected $baseDatagridClass;

    /** @var string */
    protected $acceptorClass;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var DatasourceInterface[] */
    protected $dataSources = [];

    /** @var ExtensionVisitorInterface[] */
    protected $extensions = [];

    /** @var SecurityFacade */
    protected $securityFacade;

    public function __construct(
        $baseDatagridClass,
        $acceptorClass,
        EventDispatcherInterface $eventDispatcher,
        SecurityFacade $securityFacade
    ) {
        $this->baseDatagridClass = $baseDatagridClass;
        $this->acceptorClass = $acceptorClass;
        $this->eventDispatcher = $eventDispatcher;
        $this->securityFacade = $securityFacade;
    }

    /**
     * Create, configure and build datagrid
     *
     * @param DatagridConfiguration $config
     *
     * @return DatagridInterface
     */
    public function build(DatagridConfiguration $config)
    {
        $class = $config->offsetGetByPath(self::BASE_DATAGRID_CLASS_PATH, $this->baseDatagridClass);
        $name = $config->getName();

        /** @var Acceptor $acceptor */
        $acceptor = new $this->acceptorClass($config);
        /** @var DatagridInterface $datagrid */
        $datagrid = new $class($name, $acceptor);

        $event = new BuildBefore($datagrid, $config);
        $this->eventDispatcher->dispatch(BuildBefore::NAME, $event);
        // duplicate event dispatch with grid name
        $this->eventDispatcher->dispatch(BuildBefore::NAME . '.' . $name, $event);

        $this->buildDataSource($datagrid, $config);

        foreach ($this->extensions as $extension) {
            if ($extension->isApplicable($config)) {
                $acceptor->addExtension($extension);
            }
        }

        $acceptor->processConfiguration();

        $event = new BuildAfter($datagrid);
        $this->eventDispatcher->dispatch(BuildAfter::NAME, $event);
        // duplicate event dispatch with grid name
        $this->eventDispatcher->dispatch(BuildAfter::NAME . '.' . $name, $event);

        return $datagrid;
    }

    /**
     * Register datasource type
     * Automatically registered services tagged by oro_datagrid.datasource tag
     *
     * @param string              $type
     * @param DatasourceInterface $dataSource
     *
     * @return $this
     */
    public function registerDatasource($type, DatasourceInterface $dataSource)
    {
        $this->dataSources[$type] = $dataSource;

        return $this;
    }

    /**
     * Register extension
     * Automatically registered services tagged by oro_datagrid.extension tag
     *
     * @param ExtensionVisitorInterface $extension
     *
     * @return $this
     */
    public function registerExtension(ExtensionVisitorInterface $extension)
    {
        $this->extensions[] = $extension;

        return $this;
    }

    /**
     * Try to find datasource adapter and process it
     * Datasource object should be self-acceptable to grid
     *
     * @param DatagridInterface     $grid
     * @param DatagridConfiguration $config
     *
     * @throws \RuntimeException
     */
    protected function buildDataSource(DatagridInterface $grid, DatagridConfiguration $config)
    {
        $sourceType = $config->offsetGetByPath(self::DATASOURCE_TYPE_PATH, false);
        if (!$sourceType) {
            throw new \RuntimeException('Datagrid source does not configured');
        }

        if (!isset($this->dataSources[$sourceType])) {
            throw new \RuntimeException(sprintf('Datagrid source "%s" does not exist', $sourceType));
        }

        $acl = $config->offsetGetByPath(self::DATASOURCE_ACL_PATH);
        if ($acl && !$this->isResourceGranted($acl)) {
            throw new AccessDeniedException('Access denied.');
        }

        $this->dataSources[$sourceType]->process($grid, $config->offsetGetByPath(self::DATASOURCE_PATH, []));
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
