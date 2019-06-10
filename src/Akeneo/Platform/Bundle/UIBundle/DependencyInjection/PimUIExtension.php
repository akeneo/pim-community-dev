<?php

namespace Akeneo\Platform\Bundle\UIBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

/**
 * Pim UI bundle extension
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimUIExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ .'/../Resources/config'));
        $loader->load('controllers.yml');
        $loader->load('forms.yml');
        $loader->load('locale_provider.yml');
        $loader->load('translator.yml');
        $loader->load('twig.yml');
        $loader->load('providers.yml');
        $loader->load('services.yml');
        $loader->load('view_elements.yml');

        $container->setParameter('pim_ui.wrap_class', 'block-wrap');
        $this->placeholdersConfig($config, $container);
    }

    /**
     * Add placeholders mapping
     *
     * @param array            $config
     * @param ContainerBuilder $container
     */
    protected function placeholdersConfig(array $config, ContainerBuilder $container)
    {
        $placeholders = [];
        $items = [];
        $bundles = $container->getParameter('kernel.bundles');

        foreach ($bundles as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            if (is_file($file = dirname($reflection->getFilename()) . '/Resources/config/placeholders.yml')) {
                $placeholderData = Yaml::parse(file_get_contents(realpath($file)));
                if (isset($placeholderData['placeholders'])) {
                    $placeholders = array_merge_recursive($placeholders, $placeholderData['placeholders']);
                }
                if (isset($placeholderData['items'])) {
                    $items = array_merge_recursive($items, $placeholderData['items']);
                }
            }
        }

        if (isset($config['placeholders_items']) && count($config['placeholders_items'])) {
            $placeholders = $this->overwritePlaceholders($config['placeholders_items'], $placeholders);
        }

        $placeholders = $this->addItemsToPlaceholders($placeholders, $items);

        $container->setParameter('pim_ui.placeholders', $this->changeOrders($placeholders));
    }

    /**
     * Insert items data (templates, actions, etc) into placeholders item data
     *
     * @param array $placeholders
     * @param array $items
     *
     * @return array
     */
    protected function addItemsToPlaceholders(array $placeholders, array $items)
    {
        foreach ($placeholders as $placeholderName => $placeholder) {
            if (isset($placeholder['items']) && count($placeholder['items'])) {
                foreach ($placeholder['items'] as $itemName => $itemData) {
                    if (!isset($items[$itemName])) {
                        unset($placeholders[$placeholderName]['items'][$itemName]);
                    } else {
                        if (!is_array($itemData)) {
                            $itemData = [];
                        }
                        $placeholders[$placeholderName]['items'][$itemName] = array_merge(
                            $itemData,
                            $items[$itemName],
                            ['name' => $itemName]
                        );
                    }
                }
            } else {
                $placeholder['items'] = [];
            }
        }

        return $placeholders;
    }

    /**
     * Overwrite placeholders with placeholders from the main config
     *
     * @param array $configPlaceholders
     * @param array $placeholders
     *
     * @return array
     */
    protected function overwritePlaceholders($configPlaceholders, $placeholders)
    {
        foreach ($configPlaceholders as $placeholderName => $configPlaceholder) {
            foreach ($configPlaceholder['items'] as $itemId => $item) {
                if (is_array($item) && isset($item['remove']) && $item['remove']) {
                    unset($placeholders[$placeholderName]['items'][$itemId]);
                } else {
                    if (!is_array($item) || !isset($item['order'])) {
                        $order = 1;
                    } else {
                        $order = $item['order'];
                    }
                    if (!isset($placeholders[$placeholderName])) {
                        $placeholders[$placeholderName] = ['items'=> []];
                    }
                    if (!array_key_exists($itemId, $placeholders[$placeholderName]['items'])) {
                        $placeholders[$placeholderName]['items'][$itemId] = [
                            'order' => $order
                        ];
                    } else {
                        $placeholders[$placeholderName]['items'][$itemId]['order'] = $order;
                    }
                }
            }
        }

        return $placeholders;
    }

    /**
     * Change placeholders block order
     *
     * @param array $placeholders
     *
     * @return array
     */
    protected function changeOrders(array $placeholders)
    {
        foreach ($placeholders as $placeholderName => $placeholderData) {
            if (isset($placeholders[$placeholderName]['items'])) {
                usort($placeholders[$placeholderName]['items'], [$this, "comparePlaceholderBlocks"]);
            }
        }

        return $placeholders;
    }

    /**
     * Compare function
     *
     * @param $a
     * @param $b
     *
     * @return int
     */
    protected function comparePlaceholderBlocks($a, $b)
    {
        $aOrder = 1;
        if (isset($a['order'])) {
            $aOrder = $a['order'];
        }
        $bOrder = 1;
        if (isset($b['order'])) {
            $bOrder = $b['order'];
        }

        if ($aOrder == $bOrder) {
            return 0;
        }

        return ($aOrder < $bOrder) ? -1 : 1;
    }
}
