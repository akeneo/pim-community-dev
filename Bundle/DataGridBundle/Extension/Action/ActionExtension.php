<?php

namespace Oro\Bundle\DataGridBundle\Extension\Action;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use Oro\Bundle\DataGridBundle\Extension\AbstractExtension;
use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataObject;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Extension\Action\Actions\ActionInterface;
use Oro\Bundle\DataGridBundle\Extension\Formatter\Configuration;
use Oro\Bundle\DataGridBundle\Extension\Formatter\ResultRecordInterface;

class ActionExtension extends AbstractExtension
{
    const METADATA_ACTION_KEY               = 'rowActions';
    const METADATA_ACTION_CONFIGURATION_KEY = 'action_configuration';

    const ACTION_KEY               = 'actions';
    const ACTION_CONFIGURATION_KEY = 'action_configuration';
    const ACTION_TYPE_KEY          = 'type';

    /** @var ContainerInterface */
    protected $container;

    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var TranslatorInterface */
    protected $translator;

    /** @var array */
    protected $actions = [];

    /** @var array */
    protected static $excludeParams = [ActionInterface::ACL_KEY];

    public function __construct(
        ContainerInterface $container,
        SecurityFacade $securityFacade,
        TranslatorInterface $translator
    ) {
        $this->container      = $container;
        $this->securityFacade = $securityFacade;
        $this->translator     = $translator;
    }

    /**
     * {@inheritDoc}
     */
    public function isApplicable(DatagridConfiguration $config)
    {
        $actions             = $config->offsetGetOr(self::ACTION_KEY, []);
        $actionConfiguration = $config->offsetGetOr(self::ACTION_CONFIGURATION_KEY);

        if ($actionConfiguration && is_callable($actionConfiguration)) {
            $callable = function (ResultRecordInterface $record) use ($actionConfiguration) {
                $result = call_user_func($actionConfiguration, $record);

                return is_array($result) ? $result : [];
            };

            $propertyConfig = [
                'type'     => 'callback',
                'callable' => $callable
            ];
            $config->offsetAddToArrayByPath(
                sprintf('%s[%s]', Configuration::PROPERTIES_PATH, self::METADATA_ACTION_CONFIGURATION_KEY),
                $propertyConfig
            );
        }

        return !empty($actions);
    }

    /**
     * {@inheritDoc}
     */
    public function visitMetadata(DatagridConfiguration $config, MetadataObject $data)
    {
        $actionsMetadata = [];
        $actions         = $config->offsetGetOr(self::ACTION_KEY, []);

        foreach ($actions as $name => $action) {
            $config = ActionConfiguration::createNamed($name, $action);
            $action = $this->create($config);

            if (null === $action->getAclResource() || $this->isResourceGranted($action->getAclResource())) {
                $metadata          = $action->getOptions()->toArray([], self::$excludeParams);
                $metadata['label'] = isset($metadata['label']) ? $this->translator->trans($metadata['label']) : null;
                $actionsMetadata[$config->getName()] = $metadata;
            }
        }

        $data->offsetAddToArray(self::METADATA_ACTION_KEY, $actionsMetadata);
    }

    /**
     * Register action type
     *
     * @param string $type
     * @param string $serviceId
     *
     * @return $this
     */
    public function registerAction($type, $serviceId)
    {
        $this->actions[$type] = $serviceId;

        return $this;
    }

    /**
     * Creates and configure action object
     * Services are marked as scope: prototype
     *
     * @param ActionConfiguration $config
     *
     * @throws \RunTimeException
     * @return ActionInterface
     */
    protected function create(ActionConfiguration $config)
    {
        if (!$config->offsetExists(self::ACTION_TYPE_KEY)) {
            throw new \RunTimeException('The type must be defined');
        }

        $type = $config->offsetGet(self::ACTION_TYPE_KEY);
        if (!isset($this->actions[$type])) {
            throw new \RunTimeException(
                sprintf('No attached service to action type named "%s"', $config->offsetGet(self::ACTION_TYPE_KEY))
            );
        }

        $actionServiceId = $this->actions[$type];

        /** @var $action ActionInterface */
        $action = $this->container->get($actionServiceId);
        $action->setOptions($config);

        return $action;
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
