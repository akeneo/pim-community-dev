<?php

namespace Oro\Bundle\ConfigBundle\Provider;

use Oro\Bundle\ConfigBundle\DependencyInjection\SystemConfiguration\ProcessorDecorator;

class FormProvider
{
    const TAG_NAME = 'oro_config.configuration_form_provider';

    /** @var array */
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * @param $treeName
     * @param null|string $activeTab
     * @return array
     */
    public function getFormData($treeName, $activeTab = null)
    {
        $data = array('formData' => array());

        $data['formData'] = $this->prepareTreeData($treeName);
        return $data;
    }

    /**
     * @param string $treeName
     * @throws \Exception
     * @return array
     */
    public function prepareTreeData($treeName)
    {
        if (isset($this->config[ProcessorDecorator::TREE_ROOT][$treeName])) {
            $data = $this->buildGroupNode(
                $this->config[ProcessorDecorator::TREE_ROOT][$treeName]
            );
        } else {
            throw new \Exception('Tree doesnt defined');
        }

        return $data;
    }

    protected function buildGroupNode($nodes)
    {
        foreach ($nodes as $name => $node) {
            if (is_array($node) && isset($node['children'])) {
                $group = isset($this->config[ProcessorDecorator::GROUPS_NODE][$name])
                    ? $this->config[ProcessorDecorator::GROUPS_NODE][$name] : false;

                if ($group === false) {
                    throw new \Exception(sprintf('Group "%s" doesn\'t defined', $name));
                }

                $nodes[$name] = array_merge($nodes[$name], $group);
                $nodes[$name]['children'] = $this->buildGroupNode($node['children']);
            } else {
                $nodes[$name] = $this->buildFieldNode($node);
            }
        }

        return $nodes;
    }

    protected function buildFieldNode($node)
    {
        return $node;
    }
}
