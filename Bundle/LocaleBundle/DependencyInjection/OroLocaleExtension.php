<?php

namespace Oro\Bundle\LocaleBundle\DependencyInjection;

use Oro\Bundle\LocaleBundle\Provider\LocaleSettingsProvider;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Intl\Intl;

class OroLocaleExtension extends Extension
{
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

        $this->prepareSettings($config, $container);
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
     * Prepare locale system settings default values.
     *
     * @param array $config
     * @param ContainerBuilder $container
     */
    protected function prepareSettings(array $config, ContainerBuilder $container)
    {
        $locale = $this->getDefaultLocale(
            $this->getFinalizedParameter($config['settings']['locale']['value'], $container)
        );
        $config['settings']['locale']['value'] = $locale;
        if (empty($config['settings']['language']['value'])) {
            $config['settings']['language']['value'] = $locale;
        }
        if (empty($config['settings']['country']['value'])) {
            $config['settings']['country']['value'] = $this->getDefaultCountryByLocale($locale);
        }
        $container->prependExtensionConfig('oro_locale', $config);
    }

    /**
     * Get locale default value.
     *
     * @param string $locale
     * @return string
     */
    protected function getDefaultLocale($locale)
    {
        if ($locale) {
            $displayLocaleParts = \Locale::parseLocale($locale);
            $displayPartKeys = array(\Locale::LANG_TAG, \Locale::SCRIPT_TAG, \Locale::REGION_TAG);
            $displayLocaleData = array();
            foreach ($displayPartKeys as $localePartKey) {
                if (isset($displayLocaleParts[$localePartKey])) {
                    $displayLocaleData[] = $displayLocaleParts[$localePartKey];
                }
            }
            if ($displayLocaleData) {
                return implode('_', $displayLocaleData);
            }
        }

        return LocaleSettingsProvider::DEFAULT_LOCALE;
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

        return LocaleSettingsProvider::DEFAULT_COUNTRY;
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
     * @param array|string $data
     * @return array|string
     */
    protected function escapePercentSymbols($data)
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->escapePercentSymbols($value);
            }
        } else {
            $data = str_replace('%', '%%', $data);
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
