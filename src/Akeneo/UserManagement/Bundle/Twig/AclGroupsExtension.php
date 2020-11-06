<?php

namespace Akeneo\UserManagement\Bundle\Twig;

use Symfony\Component\Yaml\Yaml;

/**
 * Twig extension to provide acl groups
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AclGroupsExtension extends \Twig_Extension
{
    /** @var array */
    protected $bundles;

    /**
     * @param array $bundles
     */
    public function __construct(array $bundles)
    {
        $this->bundles = $bundles;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new \Twig_SimpleFunction('acl_groups', fn() => $this->getAclGroups()),
            new \Twig_SimpleFunction('acl_group_names', fn() => $this->getAclGroupNames()),
        ];
    }

    /**
     * Get ACL groups.
     *
     * @return string[]
     */
    public function getAclGroups(): array
    {
        $config = $this->getConfig();

        return $this->getSortedGroups($config);
    }

    /**
     * Get ACL group names.
     *
     * @return string[]
     */
    public function getAclGroupNames(): array
    {
        $config = $this->getConfig();

        return array_keys($config);
    }

    protected function getConfig(): array
    {
        $config = [];
        foreach ($this->bundles as $class) {
            $reflection = new \ReflectionClass($class);
            $path = dirname($reflection->getFileName()) . '/Resources/config/acl_groups.yml';
            if (file_exists($path)) {
                $config = Yaml::parse(file_get_contents($path)) + $config;
            }
        }

        return $config;
    }

    /**
     * Sort the groups by their order.
     * If no order is defined for a group, it will be added after the others
     *
     * @param array $config
     */
    protected function getSortedGroups(array $config): array
    {
        $groups = $this->getGroups($config);

        return $this->sortGroups($groups);
    }

    /**
     * @param array $config
     */
    protected function getGroups(array $config): array
    {
        $groups = [];
        foreach ($config as $groupName => $groupConfig) {
            $permissionGroup = isset($groupConfig['permission_group']) ? $groupConfig['permission_group'] : 'action';

            $groups[$permissionGroup][] = [
                'name'  => $groupName,
                'order' => isset($groupConfig['order']) ? $groupConfig['order'] : -1
            ];
        }

        return $groups;
    }

    /**
     * @param array $groups
     */
    protected function sortGroups(array $groups): array
    {
        foreach (array_keys($groups) as $permissionGroup) {
            usort(
                $groups[$permissionGroup],
                function ($first, $second) {
                    if ($first['order'] === $second['order']) {
                        return 0;
                    }

                    if ($first['order'] === -1 || $second['order'] === -1) {
                        return ($first['order'] < $second['order']) ? 1 : -1;
                    }

                    return ($first['order'] < $second['order']) ? -1 : 1;
                }
            );
        }

        return $groups;
    }
}
