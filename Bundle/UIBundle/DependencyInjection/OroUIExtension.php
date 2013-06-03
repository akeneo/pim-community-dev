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

        $this->positionsConfig($config, $container);
    }

    /**
     * Add position mapping
     *
     * @param array            $config
     * @param ContainerBuilder $container
     */
    protected function positionsConfig(array $config, ContainerBuilder $container)
    {
        $positions = array();
        $bundles = $container->getParameter('kernel.bundles');
        foreach ($bundles as $bundle) {
            $reflection = new \ReflectionClass($bundle);
            if (is_file($file = dirname($reflection->getFilename()) . '/Resources/config/positions.yml')) {
                $positions = array_merge_recursive($positions, Yaml::parse(realpath($file)));
            }
        }

        $positions = $this->changeOrders($positions);

        if (isset($config['position_blocks']) && count($config['position_blocks'])) {
            $positions = $this->overwritePositions($config['position_blocks'], $positions);
        }

        $container->setParameter('oro_ui.positions', $positions);
    }

    /**
     * Overwrite positions with positions from the main config
     *
     * @param array $blocks
     * @param array $positions
     *
     * @return array
     */
    protected function overwritePositions($blocks, $positions)
    {
        foreach ($blocks as $block) {
            if (isset($block['remove']) && $block['remove']) {
                $positions = $this->removeBlock($block['name'], $positions);
            } else {
                $positions = $this->updateBlock($block, $positions);
            }
        }

        return $this->changeOrders($positions);
    }

    /**
     * Update block data
     *
     * @param array $block
     * @param array $positions
     *
     * @return array
     */
    protected function updateBlock($block, $positions)
    {
        $positions = $this->removeBlock($block['name'], $positions);
        $positions[$block['position']][] = $block;

        return $positions;
    }

    /**
     * Remove block info
     *
     * @param array $blockName
     * @param array $positions
     *
     * @return array mixed
     */
    protected function removeBlock($blockName, $positions)
    {
        foreach ($positions as $positionId => $blocks) {
            foreach ($blocks as $blockId => $block) {
                if ($block['name'] == $blockName) {
                    unset($positions[$positionId][$blockId]);
                }
            }
        }

        return $positions;
    }

    /**
     * Change position block order
     *
     * @param array $positions
     *
     * @return array
     */
    protected function changeOrders(array $positions)
    {
        foreach ($positions as $positionName => $positionBlocks) {
            foreach ($positionBlocks as $blockName => $block) {
                if (!isset($positions[$positionName][$blockName]['name'])) {
                    $positions[$positionName][$blockName]['name'] = $blockName;
                }
            }
            usort($positions[$positionName], array($this, "comparePositionBlocks"));
        }

        return $positions;
    }

    /**
     * Compare function
     *
     * @param $a
     * @param $b
     *
     * @return int
     */
    protected function comparePositionBlocks($a, $b)
    {
        $aOrder = isset($a['order']) ? $a['order'] : 0;
        $bOrder = isset($b['order']) ? $b['order'] : 0;

        if ($aOrder == $bOrder) {

            return 0;
        }

        return ($aOrder < $bOrder) ? -1 : 1;
    }
}
