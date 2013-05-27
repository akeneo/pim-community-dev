<?php

namespace Oro\Bundle\ConfigBundle\Config;

use Doctrine\Common\Persistence\ObjectManager;

use Oro\Bundle\ConfigBundle\Entity\Config;

class ConfigManager
{
    /**
     * @var ObjectManager
     */
    protected $om;

    /**
     * Settings array, initiated with global application settings
     *
     * @var array
     */
    protected $settings;

    /**
     *
     * @param ObjectManager $om
     * @param array         $settings
     */
    public function __construct(ObjectManager $om, $settings = array())
    {
        $this->om       = $om;
        $this->settings = $settings;
    }

    /**
     * Get setting value
     *
     * @param  string $name Setting name, for example "oro_user.level"
     * @return mixed
     */
    public function get($name)
    {
        $name = explode('.', $name);

        if (!isset($this->settings[$name[0]])) {
            return null;
        }

        $setting = $this->settings[$name[0]];
        $setting = isset($setting[$name[1]]) ? $setting[$name[1]] : null;

        return is_array($setting) ? $setting['value'] : $setting;
    }

    /**
     * Merge current settings with entity scoped settings
     *
     * @param string $entity   Entity name
     * @param int    $recordId Entity id
     */
    protected function mergeSettings($entity, $recordId)
    {
        $scope = $this->om->getRepository('OroConfigBundle:Config')->findOneBy(array(
            'entity'   => $entity,
            'recordId' => (int) $recordId,
        ));

        if (!$scope) {
            return;
        }

        foreach ($scope->getSettings() as $section => $settings) {
            foreach ($settings as $name => $value) {
                if (isset($this->settings[$section][$name])) {
                    $this->settings[$section][$name]['value'] = $value;
                }
            }
        }
    }
}
