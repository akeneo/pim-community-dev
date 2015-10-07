<?php

namespace Oro\Bundle\UIBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Yaml\Yaml;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class OroUIExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('oro_ui.wrap_class', $config['wrap_class']);

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
        $placeholders = array();
        $items = array();
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

        $container->setParameter('oro_ui.placeholders', $this->changeOrders($placeholders));
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
                            $itemData = array();
                        }
                        $placeholders[$placeholderName]['items'][$itemName] = array_merge(
                            $itemData,
                            $items[$itemName],
                            array('name' => $itemName)
                        );
                    }
                }
            } else {
                $placeholder['items'] = array();
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
                        $placeholders[$placeholderName] = array('items'=>array());
                    }
                    if (!array_key_exists($itemId, $placeholders[$placeholderName]['items'])) {
                        $placeholders[$placeholderName]['items'][$itemId] = array(
                            'order' => $order
                        );
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
                usort($placeholders[$placeholderName]['items'], array($this, "comparePlaceholderBlocks"));
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
