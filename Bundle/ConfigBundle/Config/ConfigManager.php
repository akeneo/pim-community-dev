<?php

namespace Oro\Bundle\ConfigBundle\Config;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\FormInterface;

use Oro\Bundle\ConfigBundle\Entity\Config;
use Oro\Bundle\ConfigBundle\Entity\ConfigValue;

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
     * @var array
     */
    protected $storedSettings = array();

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
     * @param bool $default
     * @param bool $full
     * @return mixed
     */
    public function get($name, $default = false, $full = false)
    {
        $entity = $this->getScopedEntity();
        $entityId = $this->getScopeId();

        $name = explode(self::SECTION_MODEL_SEPARATOR, $name);
        $section = $name[0];
        $key = $name[1];

        $this->loadStoredSettings($entity, $entityId);

        if ($default) {
            $settings = $this->settings;
        } elseif (isset($this->storedSettings[$entity][$entityId][$section][$key])) {
            $settings = $this->storedSettings[$entity][$entityId];
        } elseif (isset($this->settings[$section][$key])) {
            $settings = $this->settings;
        }

        if (empty($settings[$section][$key])) {
            return null;
        } else {
            $setting = $settings[$section][$key];

            return is_array($setting) && !$full ? $setting['value'] : $setting;
        }
    }

    /**
     * Save settings with fallback to global scope (default)
     */
    public function save($newSettings, $scopeEntity = null)
    {
        $repository = $this->om->getRepository('OroConfigBundle:ConfigValue');
        /** @var Config $config */
        $config = $this->om
            ->getRepository('OroConfigBundle:Config')
            ->getByEntity($scopeEntity);

        list ($updated, $removed) = $this->getChanged($newSettings);

        if (!empty($removed)) {
            $repository->removeValues($config->getId(), $removed);
        }

        foreach ($updated as $newItemKey => $newItemValue) {
            $newItemKey = explode(self::SECTION_VIEW_SEPARATOR, $newItemKey);
            $newItemValue = is_array($newItemValue) ? $newItemValue['value'] : $newItemValue;

            /** @var ConfigValue $value */
            $value = $config->getOrCreateValue($newItemKey[0], $newItemKey[1]);
            $value->setValue($newItemValue);

            $config->getValues()->add($value);
        }

        $this->om->persist($config);
        $this->om->flush();
    }

    /**
     * @param $newSettings
     * @return array
     */
    public function getChanged($newSettings)
    {
        // find new and updated
        $updated = array();
        $removed = array();
        foreach ($newSettings as $key => $value) {
            $currentValue = $this->get(str_replace(self::SECTION_VIEW_SEPARATOR, self::SECTION_MODEL_SEPARATOR, $key), false, true);

            // save only if setting exists and there's no default checkbox checked
            if (!is_null($currentValue) && empty($value['use_parent_scope_value'])) {
                $updated[$key] = $value;
            }

            $valueDefined = isset($currentValue['use_parent_scope_value']) && $currentValue['use_parent_scope_value'] == false;
            $valueStillDefined = isset($value['use_parent_scope_value']) && $value['use_parent_scope_value'] == false;
            if ($valueDefined && !$valueStillDefined) {
                $key = explode(self::SECTION_VIEW_SEPARATOR, $key);
                $removed[] = array($key[0], $key[1]);
            }
        }

        return array($updated, $removed);
    }

    /**
     * @param $entity
     * @param $entityId
     * @param null $section
     * @return array
     */
    protected function loadStoredSettings($entity, $entityId, $section = null)
    {
        if ($section && !empty($this->storedSettings[$entity][$entityId][$section])) {
            return $this->storedSettings[$entity][$entityId][$section];
        }

        if (!empty($this->storedSettings[$entity][$entityId])) {
            return $this->storedSettings[$entity][$entityId];
        }

        $settings = $this->om
            ->getRepository('OroConfigBundle:Config')
            ->loadSettings($entity, $entityId, $section);

        if (empty($this->storedSettings[$entity][$entityId])) {
            $this->storedSettings[$entity][$entityId] = array();
        }
        $this->storedSettings[$entity][$entityId] = array_merge($this->storedSettings[$entity][$entityId], $settings);
    }

    /**
     * @param FormInterface $form
     *
     * @return array
     */
    public function getSettingsByForm(FormInterface $form)
    {
        $settings = array();

        foreach ($form as $child) {
            $key = str_replace(
                    self::SECTION_VIEW_SEPARATOR,
                    self::SECTION_MODEL_SEPARATOR,
                    $child->getName()
                );
            $settings[$child->getName()] = $this->get($key, false, true);

            $settings[$child->getName()]['use_parent_scope_value'] =
                !isset($settings[$child->getName()]['use_parent_scope_value']) ?
                    true : $settings[$child->getName()]['use_parent_scope_value'];

        }

        return $settings;
    }

    /**
     * @return null
     */
    public function getScopedEntity()
    {
        return null;
    }

    /**
     * @return int
     */
    public function getScopeId()
    {
        return null;
    }
}
