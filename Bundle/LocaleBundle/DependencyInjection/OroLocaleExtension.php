<?php

namespace Oro\Bundle\LocaleBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Intl\Intl;

class OroLocaleExtension extends Extension
{
    const DEFAULT_COUNTRY = 'US';

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $locale = $this->getFinalizedParameter($config['settings']['locale']['value'], $container);
        $config['settings']['locale']['value'] = $this->getDefaultLocale($locale);
        if (empty($config['settings']['language']['value'])) {
            $config['settings']['language']['value'] = $this->getDefaultLanguageByLocale($locale);
        }
        if (empty($config['settings']['country']['value'])) {
            $config['settings']['country']['value'] = $this->getDefaultCountryByLocale($locale);
        }
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
    protected function getDefaultLanguageByLocale($locale)
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
    protected function getDefaultLocale($locale)
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
     * Get country name based on locale.
     *
     * @param string $locale
     * @return string
     */
    protected function getDefaultCountryByLocale($locale)
    {
        $region = \Locale::getRegion($locale);
        $countries = Intl::getRegionBundle()->getCountryNames();
        if (array_key_exists($region, $countries)) {
            return $region;
        }
        return static::DEFAULT_COUNTRY;
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
