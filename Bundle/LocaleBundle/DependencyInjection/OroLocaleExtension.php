<?php

namespace Oro\Bundle\LocaleBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Intl\Intl;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class OroLocaleExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $config['settings']['locale']['value'] = $this->getDisplayLocale(
            $this->getFinalizedParameter($config['settings']['locale']['value'], $container)
        );
        $config['settings']['language']['value'] = $this->getDisplayLanguage(
            $this->getFinalizedParameter($config['settings']['language']['value'], $container)
        );
        $container->prependExtensionConfig('oro_locale', $config);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    /**
     * Get language default value.
     *
     * @param string $locale
     * @return string
     */
    protected function getDisplayLanguage($locale)
    {
        $displayLocaleParts = \Locale::parseLocale($locale);
        $language = $displayLocaleParts[\Locale::LANG_TAG];
        $region = isset($displayLocaleParts[\Locale::REGION_TAG]) ? $displayLocaleParts[\Locale::REGION_TAG] : null;
        $availableLanguages = Intl::getLanguageBundle()->getLanguageNames();

        $regionLang = $language . '_' . $region;
        if (array_key_exists($regionLang, $availableLanguages)) {
            $language = $regionLang;
        }

        return $language;
    }

    /**
     * Get locale default value.
     *
     * @param string $locale
     * @return string
     */
    protected function getDisplayLocale($locale)
    {
        $displayLocaleParts = \Locale::parseLocale($locale);
        $displayPartKeys = array('language', 'script', 'region');
        $displayLocaleData = array();
        foreach ($displayPartKeys as $localePartKey) {
            if (isset($displayLocaleParts[$localePartKey])) {
                $displayLocaleData[] = $displayLocaleParts[$localePartKey];
            }
        }
        return implode('_', $displayLocaleData);
    }

    /**
     * @param string $parameter
     * @param ContainerBuilder $container
     * @return mixed
     */
    protected function getFinalizedParameter($parameter, ContainerBuilder $container)
    {
        if (is_string($parameter) && strpos($parameter, '%') === 0) {
            return $container->getParameter(str_replace('%', '', $parameter));
        }
        return $parameter;
    }
}
