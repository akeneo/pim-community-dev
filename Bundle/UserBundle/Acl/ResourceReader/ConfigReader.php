<?php
namespace Oro\Bundle\UserBundle\Acl\ResourceReader;

use Symfony\Component\Yaml\Yaml;
use Oro\Bundle\UserBundle\Annotation\Acl;

class ConfigReader
{
    /**
     * @var array
     */
    private $bundles;

    public function __construct(array $bundles)
    {
        $this->bundles = $bundles;
    }

    /**
     * Get ACL Resources array from config files
     *
     * @param string $directory
     *
     * @return \Oro\Bundle\UserBundle\Annotation\Aclarray
     */
    public function getConfigResources($directory = '')
    {
        $aclResources = array();
        $aclConfig = $this->getConfigAclArray($directory);
        if (count($aclConfig)) {
            foreach ($aclConfig as $id => $acl) {
                $aclObject = new Acl(
                    array(
                         'id'          => $id,
                         'name'        => $acl['name'],
                         'description' => $acl['description'],
                         'parent'      => isset($acl['parent']) ? $acl['parent'] : false
                    )
                );
                if (isset($acl['method']) && isset($acl['class'])) {
                    $aclObject->setMethod($acl['method']);
                    $aclObject->setClass($acl['class']);
                }

                $aclResources[$id] = $aclObject;
            }
        }

        return $aclResources;
    }

    /**
     * Get
     *
     * @param string $className
     * @param string $methodName
     *
     * @return bool|string
     */
    public function getMethodAclId($className, $methodName)
    {
        $aclConfig = $this->getConfigAclArray();
        foreach ($aclConfig as $id => $acl) {
            if (isset($acl['class']) && isset($acl['method'])
                && $acl['class'] == $className && $acl['method'] == $methodName) {
                return $id;
            }
        }

        return false;
    }

    /**
     * Get ACL array from config files
     *
     * @param string $directory
     *
     * @return array
     */
    protected function getConfigAclArray($directory = '')
    {
        $aclConfig = array();
        if (!$directory) {
            foreach ($this->bundles as $bundle) {
                $reflection = new \ReflectionClass($bundle);
                $aclConfig += $this->parseAclConfigFile($reflection->getFilename());
            }
        } else {
            $aclConfig += $this->parseAclConfigFile($directory);
        }

        return $aclConfig;
    }

    /**
     * Get ACL resources from config files of bundle
     *
     * @param string $directory
     *
     * @return array
     */
    protected function parseAclConfigFile($directory){
        if (is_file($file = dirname($directory) . '/Resources/config/acl.yml')) {
            return Yaml::parse(realpath($file));
        }

        return array();
    }
}
