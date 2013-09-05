<?php

namespace Oro\Bundle\ConfigBundle\Config;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\PersistentCollection;
use Oro\Bundle\ConfigBundle\Entity\Config;

class ConfigManager
{
    const SECTION_VIEW_SEPARATOR  = '___';
    const SECTION_MODEL_SEPARATOR = '.';

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
        $name = explode(self::SECTION_MODEL_SEPARATOR, $name);

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
        $flatSettings = $this->getFlatSettings($this->settings);

        // new settings
        $new = array_diff($this->getFlatSettings($newSettings, true), $flatSettings);

        $updated = array();
        foreach ($this->settings as $section => $settings) {
            foreach ($settings as $key => $value) {
                // removed/reverted to default values
                // fallback to global setting - remove scoped value
                $newKey = $section.'____'.$key;
                if (!empty($newSettings[$newKey]['use_parent_scope_value'])) {
                    $remove[] = array($section, $key);
                }

                // updated
                if (!empty($newSettings[$newKey]) && $newSettings[$newKey] != $value) {
                    $updated[] = array(
                        $section,
                        $key,
                        $newSettings[$newKey]
                    );
                }
            }
        }

        // find scope config
        $scopedId = $scopeEntity ? $scopeEntity->getId() : null;
        $config = $this->om
            ->getRepository('Oro\Bundle\ConfigBundle\Entity\Config')
            ->findOneBy(array('scopedEntity' => $scopeEntity, 'recordId' => $scopedId));

        if (!$config) {
            $config = new Config();
            $config
                ->setEntity($scopeEntity)
                ->setRecordId($scopedId);
        }

        /** @var PersistentCollection $values */
        $values = $config->getValues();
        foreach ($new as $newItemKey => $newItemValue) {
            //$values->
        }

        return;

        $this->om->persist($config);
        $this->om->flush();
    }

    /**
     * @param $settingsArray
     * @param bool $sectionsMerged
     * @return array
     */
    public function getFlatSettings($settingsArray, $sectionsMerged = false)
    {
        $_settings = array();

        if ($sectionsMerged) {
            foreach ($settingsArray as $key => $value) {
                $_settings[$key] = $value['value'];
            }
        } else {
            foreach ($settingsArray as $section => $settings) {
                foreach ($settings as $key => $value) {
                    $_settings[$section . self::SECTION_VIEW_SEPARATOR . $key] = $value['value'];
                }
            }
        }

        return $_settings;
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
