<?php

namespace Oro\Bundle\ConfigBundle\Config;

use Doctrine\Common\Persistence\ObjectManager;

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
     * @param null|object $scopeEntity that may represent user, group, etc
     * @return mixed
     */
    public function get($name, $scopeEntity = null)
    {
        $name = explode('.', $name);

        if ($scopeEntity) {
            $settings = $this->getMergedSettings(get_class($scopeEntity), $scopeEntity->getId());
        } else {
            $settings = $this->settings;
        }

        if (!isset($settings[$name[0]])) {
            return null;
        }

        $setting = $settings[$name[0]];
        $setting = isset($setting[$name[1]]) ? $setting[$name[1]] : null;

        return is_array($setting) ? $setting['value'] : $setting;
    }

    /**
     * Save settings with fallback to global scope (default)
     */
    public function save($newSettings, $scopeEntity = null)
    {
        $remove = array();
        $flatSettings = $this->getFlatSettings();

        // new settings
        $new = array_diff($newSettings, $flatSettings);

        $updated = array();
        foreach ($this->settings as $section => $settings) {
            foreach ($settings as $key => $value) {
                // removed/reverted to default values
                // fallback to global setting - remove scoped value
                if (!empty($newSettings[$section][$key]['is_default']) && $scopeEntity) {
                    $remove[] = array($section, $key, get_class($scopeEntity), $scopeEntity->getId());
                }

                // updated
                if (!empty($newSettings[$section][$key]) && $newSettings[$section][$key] != $value) {
                    $updated[] = array(
                        $section,
                        $key,
                        get_class($scopeEntity),
                        $scopeEntity->getId(),
                        $newSettings[$section][$key]
                    );
                }
            }
        }

        return;



        $this->om->persist($config);
        $this->om->flush();
    }

    /**
     * @return array
     */
    public function getFlatSettings()
    {
        $settings = array();

        foreach ($this->settings as $section => $settings) {
            foreach ($settings as $key => $value) {
                $settings[$section.':'.$key] = $value;
            }
        }

        return $settings;
    }

    /**
     * Merge current settings with entity scoped settings
     *
     * @param string $entity   Entity name
     * @param int    $recordId Entity id
     */
    protected function mergeSettings($entity, $recordId)
    {
        $this->settings = $this->getMergedSettings($entity, $recordId);
    }

    /**
     * @param string $entity Entity name
     * @param int $recordId
     * @param null|string $section section name, if specified - only this one processed
     * @return array
     */
    protected function getMergedSettings($entity, $recordId, $section = null)
    {
        $scope = $this->om->getRepository('OroConfigBundle:Config')->findOneBy(
            array(
                'entity'   => $entity,
                'recordId' => (int) $recordId,
            )
        );

        if (!$scope) {
            return $this->settings;
        }

        $mergedSettings = $this->settings;
        foreach ($scope->getSettings() as $section => $settings) {
            foreach ($settings as $name => $value) {
                if (isset($this->settings[$section][$name])) {
                    $mergedSettings[$section][$name]['value'] = $value;
                }
            }
        }

        // TODO: get settings by section from db

        return empty($mergedSettings[$section]) ? $mergedSettings : $mergedSettings[$section];
    }
}
