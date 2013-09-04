<?php

namespace Oro\Bundle\ConfigBundle\Utils;

final class TreeUtils
{
    /**
     * Finds node by name in tree
     * called recursively
     *
     * @param array $nodes
     * @param string $nodeName
     * @return null|array
     */
    public static function findNodeByName($nodes, $nodeName)
    {
        foreach ($nodes as $node) {
            if (isset($node['name']) && $node['name'] === $nodeName) {
                return $node;
            } elseif (!empty($node['children'])) {
                return static::findNodeByName($node['children'], $nodeName);
            }
        }

        return null;
    }

    /**
     * Pick nodes for needed level
     * called recursively
     *
     * @param array $nodes
     * @param int $neededLevel
     * @param int $currentLevel
     * @return null
     */
    public static function getByNestingLevel($nodes, $neededLevel, $currentLevel = 0)
    {
        $currentLevel++;
        foreach ($nodes as $node) {
            if (!empty($node['children'])) {
                if ($neededLevel === $currentLevel) {
                    return $node['children'];
                } else {
                    return static::getByNestingLevel($node['children'], $neededLevel, $currentLevel);
                }
            }
        }
        return null;
    }

    /**
     * Provides userfunc sort by 'priority' property
     *
     * @param array $nodes
     * @return array
     */
    public static function sortNodesByPriority($nodes)
    {
        usort(
            $nodes,
            function ($a, $b) {
                return ($a['priority'] < $b['priority']) ? -1 : 1;
            }
        );
    }
}
