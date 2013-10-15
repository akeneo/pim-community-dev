<?php

namespace Oro\Bundle\LocaleBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Intl\Intl;

class OroLocaleExtension extends Extension
{
    const DEFAULT_COUNTRY = 'US';

    const PARAMETER_NAME_FORMATS = 'oro_locale.format.name';
    const PARAMETER_ADDRESS_FORMATS = 'oro_locale.format.address';

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configs = $this->processNameAndAddressFormatConfiguration($configs, $container);
        
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

        $container->setParameter(
            self::PARAMETER_NAME_FORMATS,
            $this->escapePercentSymbols($config['name_format'])
        );
        $container->setParameter(
            self::PARAMETER_ADDRESS_FORMATS,
            $this->escapePercentSymbols($config['address_format'])
        );

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

    /**
     * @param array $data
     * @return array
     */
    protected function escapePercentSymbols(array $data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->escapePercentSymbols($value);
            } else {
                $data[$key] = str_replace('%', '%%', $value);
            }
        }

        return $data;
    }

    /**
     * @param array $configs
     * @param ContainerBuilder $container
     * @return array
     */
    protected function processNameAndAddressFormatConfiguration(array $configs, ContainerBuilder $container)
    {
        $externalNameFormat = array();
        $externalAddressFormat = array();

        // read formats
        foreach ($container->getParameter('kernel.bundles') as $bundle) {
            $reflection = new \ReflectionClass($bundle);

            // read name format files
            if (file_exists($file = dirname($reflection->getFilename()) . '/Resources/config/name_format.yml')) {
                $externalNameFormat = array_merge($externalNameFormat, Yaml::parse(realpath($file)));
            }

            // read address format files
            if (file_exists($file = dirname($reflection->getFilename()) . '/Resources/config/address_format.yml')) {
                $externalAddressFormat = array_merge($externalAddressFormat, Yaml::parse(realpath($file)));
            }
        }

        if (!empty($configs)) {
            $configData = array_shift($configs);
        } else {
            $configData = array();
        }

        // merge formats
        if (!empty($configData['name_format'])) {
            $configData['name_format'] = array_merge($externalNameFormat, $configData['name_format']);
        } else {
            $configData['name_format'] = $externalNameFormat;
        }

        if (!empty($configData['address_format'])) {
            $configData['address_format'] = array_merge($externalAddressFormat, $configData['address_format']);
        } else {
            $configData['address_format'] = $externalAddressFormat;
        }

        array_unshift($configs, $configData);

        return $configs;
    }
}
